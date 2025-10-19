-- Create portfolio_items table
CREATE TABLE IF NOT EXISTS `portfolio_items` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `creator_id` INT(10) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `image_path` VARCHAR(500),
  `project_url` VARCHAR(500),
  `technologies` TEXT,
  `display_order` INT(11) DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`),
  KEY `is_featured` (`is_featured`),
  CONSTRAINT `portfolio_items_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `creator_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
