-- Update script for Services component version 1.0.3
-- Add short and long description fields and migrate data

ALTER TABLE `#__services_items`
  ADD COLUMN `short_description` TEXT NULL AFTER `category_id`,
  ADD COLUMN `long_description` MEDIUMTEXT NULL AFTER `short_description`;

-- Migrate existing description into the new long_description and derive a short summary
UPDATE `#__services_items`
  SET `long_description` = CASE WHEN `long_description` IS NULL OR `long_description` = '' THEN `description` ELSE `long_description` END,
      `short_description` = CASE 
          WHEN (`short_description` IS NULL OR `short_description` = '') AND `description` IS NOT NULL THEN LEFT(`description`, 220)
          ELSE `short_description`
      END;