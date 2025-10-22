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
  KEY `idx_created_by` (`created_by`),
  KEY `idx_state` (`state`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_rating` (`rating_avg`),
  UNIQUE KEY `uniq_alias` (`alias`),
  CONSTRAINT `fk_services_items_created_by` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_services_items_category` FOREIGN KEY (`category_id`) REFERENCES `#__categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  KEY `idx_rating` (`rating`),
  KEY `idx_state` (`state`),
  KEY `idx_created` (`created`),
  CONSTRAINT `fk_services_reviews_service` FOREIGN KEY (`service_id`) REFERENCES `#__services_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_services_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `#__users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  KEY `idx_receiver` (`receiver_id`),
  KEY `idx_created` (`created`),
  KEY `idx_seen` (`seen`),
  CONSTRAINT `fk_services_messages_service` FOREIGN KEY (`service_id`) REFERENCES `#__services_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_services_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `#__users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_services_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `#__users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  UNIQUE KEY `uniq_user` (`user_id`),
  KEY `idx_business_name` (`business_name`),
  KEY `idx_created` (`created`),
  CONSTRAINT `fk_services_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `#__users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default service categories with proper nested set values
-- First, get the root category ID for com_services
SET @root_id = 1;

-- Insert categories with temporary lft/rgt values (will be fixed by rebuild)
INSERT IGNORE INTO `#__categories` (`asset_id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `extension`, `title`, `alias`, `description`, `published`, `access`, `params`, `created_user_id`, `created_time`, `language`, `version`) VALUES
(0, @root_id, 2, 3, 1, 'plumbing', 'com_services', 'Plumbing', 'plumbing', 'Plumbing and water-related services', 1, 1, '{}', 42, NOW(), '*', 1),
(0, @root_id, 4, 5, 1, 'electrical', 'com_services', 'Electrical', 'electrical', 'Electrical installation and repair services', 1, 1, '{}', 42, NOW(), '*', 1),
(0, @root_id, 6, 7, 1, 'construction', 'com_services', 'Construction', 'construction', 'Construction and building services', 1, 1, '{}', 42, NOW(), '*', 1),
(0, @root_id, 8, 9, 1, 'cleaning', 'com_services', 'Cleaning', 'cleaning', 'Cleaning and maintenance services', 1, 1, '{}', 42, NOW(), '*', 1),
(0, @root_id, 10, 11, 1, 'landscaping', 'com_services', 'Landscaping', 'landscaping', 'Landscaping and garden services', 1, 1, '{}', 42, NOW(), '*', 1),
(0, @root_id, 12, 13, 1, 'automotive', 'com_services', 'Automotive', 'automotive', 'Car repair and automotive services', 1, 1, '{}', 42, NOW(), '*', 1),
(0, @root_id, 14, 15, 1, 'home-services', 'com_services', 'Home Services', 'home-services', 'General home improvement and repair services', 1, 1, '{}', 42, NOW(), '*', 1);
