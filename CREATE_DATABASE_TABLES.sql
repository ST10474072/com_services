-- Custom Tools Component - Database Installation Script
-- Run this SQL script manually if the tables weren't created during installation
-- Replace 'jos_' with your actual Joomla table prefix

-- Drop existing tables if they exist (optional - only if you want to recreate)
-- DROP TABLE IF EXISTS `jos_services_profiles`;
-- DROP TABLE IF EXISTS `jos_services_messages`;
-- DROP TABLE IF EXISTS `jos_services_reviews`;
-- DROP TABLE IF EXISTS `jos_services_items`;

-- Services Items Table
CREATE TABLE IF NOT EXISTS `jos_services_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `category_id` INT DEFAULT NULL,
  `description` MEDIUMTEXT,
  `logo` VARCHAR(255) DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `lat` DECIMAL(10,7) DEFAULT NULL,
  `lng` DECIMAL(10,7) DEFAULT NULL,
  `is_247` TINYINT(1) DEFAULT 0,
  `is_emergency` TINYINT(1) DEFAULT 0,
  `rating_avg` DECIMAL(3,1) DEFAULT 0.0,
  `reviews_count` INT DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `featured_until` DATETIME NULL,
  `created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_by` INT NOT NULL,
  `modified` DATETIME NULL,
  `modified_by` INT NULL,
  `state` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_featured_until` (`featured_until`),
  KEY `idx_latlng` (`lat`,`lng`),
  KEY `idx_state` (`state`),
  KEY `idx_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services Reviews Table
CREATE TABLE IF NOT EXISTS `jos_services_reviews` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `service_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `rating` DECIMAL(2,1) NOT NULL,
  `comment` MEDIUMTEXT,
  `helpful` INT DEFAULT 0,
  `created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `state` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_state` (`state`),
  KEY `idx_created` (`created`),
  FOREIGN KEY (`service_id`) REFERENCES `jos_services_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `jos_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services Messages Table
CREATE TABLE IF NOT EXISTS `jos_services_messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `thread_id` VARCHAR(64) NOT NULL,
  `service_id` INT NOT NULL,
  `sender_id` INT NOT NULL,
  `receiver_id` INT NOT NULL,
  `body` MEDIUMTEXT,
  `attachment` VARCHAR(255) DEFAULT NULL,
  `created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `seen` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_thread` (`thread_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_receiver` (`receiver_id`),
  KEY `idx_created` (`created`),
  FOREIGN KEY (`service_id`) REFERENCES `jos_services_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `jos_users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `jos_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services Profiles Table
CREATE TABLE IF NOT EXISTS `jos_services_profiles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `business_name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(64) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `whatsapp` VARCHAR(64) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `about` MEDIUMTEXT,
  `address` VARCHAR(255) DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `modified` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user` (`user_id`),
  KEY `idx_created` (`created`),
  FOREIGN KEY (`user_id`) REFERENCES `jos_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data (optional)
INSERT IGNORE INTO `jos_services_items` 
(`title`, `alias`, `description`, `location`, `created_by`, `state`) 
VALUES 
('Sample Plumbing Service', 'sample-plumbing-service', 'Professional plumbing services for your home and business.', 'New York, NY', 1, 1),
('Elite Electrical', 'elite-electrical', 'Licensed electricians providing quality electrical services.', 'Los Angeles, CA', 1, 1),
('Quality Construction', 'quality-construction', 'Full-service construction and renovation company.', 'Chicago, IL', 1, 1);

-- Success message
SELECT 'Custom Tools Component database tables created successfully!' as Status;