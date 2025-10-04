<?php

// Database connection function
function get_db_connection() {
    static $conn = null;

    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            log_db_error('Database connection failed: ' . $conn->connect_error);
            die('Database connection failed. Please try again later.');
        }

        $conn->set_charset('utf8mb4');
    }

    // Check if connection is still alive
    if (!$conn->ping()) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
    }

    return $conn;
}

function db_query($sql) {
    $conn = get_db_connection();
    $result = $conn->query($sql);

    if (!$result) {
        log_db_error('Query failed: ' . $conn->error . ' | SQL: ' . $sql);
    }

    return $result;
}

function db_prepare($sql) {
    $conn = get_db_connection();
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        log_db_error('Prepare failed: ' . $conn->error . ' | SQL: ' . $sql);
    }

    return $stmt;
}

function db_escape($value) {
    $conn = get_db_connection();
    return $conn->real_escape_string($value);
}

function db_last_insert_id() {
    $conn = get_db_connection();
    return $conn->insert_id;
}

function db_affected_rows() {
    $conn = get_db_connection();
    return $conn->affected_rows;
}

function log_db_error($message) {
    $logFile = LOG_PATH . '/database.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    @file_put_contents($logFile, $logMessage, FILE_APPEND);
}
