-- Update script for Services component version 1.0.1
-- This script ensures tables exist and updates the component

-- Ensure services_items table exists with all required columns
CREATE TABLE IF NOT EXISTS `#__services_items` (
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
  KEY `idx_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ensure services_reviews table exists
CREATE TABLE IF NOT EXISTS `#__services_reviews` (
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
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ensure services_messages table exists
CREATE TABLE IF NOT EXISTS `#__services_messages` (
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
  KEY `idx_receiver` (`receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ensure services_profiles table exists
CREATE TABLE IF NOT EXISTS `#__services_profiles` (
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
  UNIQUE KEY `uniq_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add any missing indexes for better performance
-- Note: Joomla's database checker doesn't understand "IF NOT EXISTS" here,
-- so we add the indexes directly in the update script.
ALTER TABLE `#__services_items` ADD INDEX `idx_alias` (`alias`);
ALTER TABLE `#__services_items` ADD INDEX `idx_created` (`created`);
ALTER TABLE `#__services_reviews` ADD INDEX `idx_created` (`created`);
ALTER TABLE `#__services_messages` ADD INDEX `idx_created` (`created`);
