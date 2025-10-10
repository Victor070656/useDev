<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "Running database migrations...\n\n";

try {
    $conn = get_db_connection();

    // Get all SQL migration files
    $migrationsDir = __DIR__ . '/../migrations';

    if (!is_dir($migrationsDir)) {
        die("Migrations directory not found: $migrationsDir\n");
    }

    $migrationFiles = glob($migrationsDir . '/*.sql');

    if (empty($migrationFiles)) {
        echo "No migration files found in $migrationsDir\n";
        exit(0);
    }

    sort($migrationFiles); // Execute in order

    foreach ($migrationFiles as $migrationFile) {
        $filename = basename($migrationFile);
        echo "Processing migration: $filename\n";

        $sql = file_get_contents($migrationFile);

        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
            }
        );

        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                // Show first 60 chars of statement
                $preview = substr(trim($statement), 0, 60);
                echo "  Executing: " . $preview . (strlen($statement) > 60 ? '...' : '') . "\n";

                if (!$conn->query($statement)) {
                    throw new Exception("Query failed: " . $conn->error . "\nStatement: " . $statement);
                }
            }
        }

        echo "  ✓ Migration $filename completed successfully\n\n";
    }

    echo "\n✓✓✓ All migrations completed successfully! ✓✓✓\n";
    echo "\nDatabase is ready to use.\n";

} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
