<?php
/**
 * @package     Services Component
 * @subpackage  Administrator
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Database Helper Class for Services Component
 * 
 * @since  1.0.0
 */
class ServicesHelperDatabase
{
    /**
     * Check if all required database tables exist
     *
     * @return  boolean  True if all tables exist
     * @since   1.0.0
     */
    public static function checkTables()
    {
        $db = Factory::getDbo();
        
        // Define required tables
        $tables = array(
            '#__services',
            '#__services_reviews', 
            '#__services_messages',
            '#__services_categories'
        );
        
        try {
            foreach ($tables as $table) {
                // Check if table exists
                $query = "SHOW TABLES LIKE " . $db->quote($db->replacePrefix($table));
                $db->setQuery($query);
                $result = $db->loadResult();
                
                if (!$result) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Create missing database tables
     *
     * @return  boolean  True on success
     * @since   1.0.0
     */
    public static function createTables()
    {
        $db = Factory::getDbo();
        
        try {
            // Create services table
            $query = "CREATE TABLE IF NOT EXISTS `#__services` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `alias` varchar(255) NOT NULL,
                `description` text,
                `logo` varchar(255) DEFAULT NULL,
                `location` varchar(255) DEFAULT NULL,
                `latitude` decimal(10,8) DEFAULT NULL,
                `longitude` decimal(11,8) DEFAULT NULL,
                `is_247` tinyint(1) DEFAULT 0,
                `is_emergency` tinyint(1) DEFAULT 0,
                `is_featured` tinyint(1) DEFAULT 0,
                `featured_until` datetime DEFAULT NULL,
                `state` tinyint(1) DEFAULT 0,
                `created` datetime DEFAULT CURRENT_TIMESTAMP,
                `created_by` int(11) DEFAULT 0,
                `modified` datetime DEFAULT NULL,
                `modified_by` int(11) DEFAULT 0,
                `rating_avg` decimal(3,2) DEFAULT 0.00,
                `reviews_count` int(11) DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `idx_state` (`state`),
                KEY `idx_featured` (`is_featured`),
                KEY `idx_created` (`created`),
                KEY `idx_location` (`location`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $db->setQuery($query);
            $db->execute();
            
            // Create reviews table
            $query = "CREATE TABLE IF NOT EXISTS `#__services_reviews` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `service_id` int(11) unsigned NOT NULL,
                `user_id` int(11) DEFAULT 0,
                `rating` tinyint(1) NOT NULL,
                `comment` text,
                `helpful_count` int(11) DEFAULT 0,
                `state` tinyint(1) DEFAULT 0,
                `created` datetime DEFAULT CURRENT_TIMESTAMP,
                `created_by` int(11) DEFAULT 0,
                `modified` datetime DEFAULT NULL,
                `modified_by` int(11) DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `idx_service` (`service_id`),
                KEY `idx_user` (`user_id`),
                KEY `idx_state` (`state`),
                KEY `idx_created` (`created`),
                FOREIGN KEY (`service_id`) REFERENCES `#__services` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $db->setQuery($query);
            $db->execute();
            
            // Create messages table
            $query = "CREATE TABLE IF NOT EXISTS `#__services_messages` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `thread_id` varchar(100) DEFAULT NULL,
                `service_id` int(11) unsigned DEFAULT NULL,
                `sender_id` int(11) DEFAULT 0,
                `receiver_id` int(11) DEFAULT 0,
                `subject` varchar(255) DEFAULT NULL,
                `body` text NOT NULL,
                `attachment` varchar(255) DEFAULT NULL,
                `seen` tinyint(1) DEFAULT 0,
                `state` tinyint(1) DEFAULT 1,
                `created` datetime DEFAULT CURRENT_TIMESTAMP,
                `created_by` int(11) DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `idx_thread` (`thread_id`),
                KEY `idx_service` (`service_id`),
                KEY `idx_sender` (`sender_id`),
                KEY `idx_receiver` (`receiver_id`),
                KEY `idx_seen` (`seen`),
                KEY `idx_created` (`created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $db->setQuery($query);
            $db->execute();
            
            // Create categories table
            $query = "CREATE TABLE IF NOT EXISTS `#__services_categories` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `alias` varchar(255) NOT NULL,
                `description` text,
                `state` tinyint(1) DEFAULT 1,
                `created` datetime DEFAULT CURRENT_TIMESTAMP,
                `created_by` int(11) DEFAULT 0,
                `modified` datetime DEFAULT NULL,
                `modified_by` int(11) DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `idx_state` (`state`),
                KEY `idx_alias` (`alias`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $db->setQuery($query);
            $db->execute();
            
            return true;
            
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('COM_SERVICES_DATABASE_ERROR', $e->getMessage()),
                'error'
            );
            return false;
        }
    }
    
    /**
     * Get database statistics for dashboard
     *
     * @return  object  Statistics object
     * @since   1.0.0
     */
    public static function getStats()
    {
        $db = Factory::getDbo();
        $stats = new stdClass();
        
        try {
            // Total services
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__services')
                ->where('state >= 0');
            $db->setQuery($query);
            $stats->totalServices = (int) $db->loadResult();
            
            // Total reviews
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__services_reviews')
                ->where('state >= 0');
            $db->setQuery($query);
            $stats->totalReviews = (int) $db->loadResult();
            
            // Total messages
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__services_messages')
                ->where('state >= 0');
            $db->setQuery($query);
            $stats->totalMessages = (int) $db->loadResult();
            
            // Featured services
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__services')
                ->where('state >= 0')
                ->where('is_featured = 1');
            $db->setQuery($query);
            $stats->featuredServices = (int) $db->loadResult();
            
            // Services this month
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__services')
                ->where('state >= 0')
                ->where('created >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)');
            $db->setQuery($query);
            $stats->servicesThisMonth = (int) $db->loadResult();
            
            // Average rating
            $query = $db->getQuery(true)
                ->select('AVG(rating_avg)')
                ->from('#__services')
                ->where('state >= 0')
                ->where('rating_avg > 0');
            $db->setQuery($query);
            $stats->averageRating = (float) $db->loadResult();
            
            return $stats;
            
        } catch (Exception $e) {
            // Return empty stats if tables don't exist
            $stats->totalServices = 0;
            $stats->totalReviews = 0;
            $stats->totalMessages = 0;
            $stats->featuredServices = 0;
            $stats->servicesThisMonth = 0;
            $stats->averageRating = 0.0;
            
            return $stats;
        }
    }
    
    /**
     * Get recent services for dashboard
     *
     * @param   int  $limit  Number of items to return
     * @return  array  Array of recent services
     * @since   1.0.0
     */
    public static function getRecentServices($limit = 5)
    {
        $db = Factory::getDbo();
        
        try {
            $query = $db->getQuery(true)
                ->select('s.*, u.name as author')
                ->from('#__services AS s')
                ->leftJoin('#__users AS u ON u.id = s.created_by')
                ->where('s.state >= 0')
                ->order('s.created DESC')
                ->setLimit($limit);
            
            $db->setQuery($query);
            return $db->loadObjectList();
            
        } catch (Exception $e) {
            return array();
        }
    }
    
    /**
     * Get recent reviews for dashboard
     *
     * @param   int  $limit  Number of items to return
     * @return  array  Array of recent reviews
     * @since   1.0.0
     */
    public static function getRecentReviews($limit = 5)
    {
        $db = Factory::getDbo();
        
        try {
            $query = $db->getQuery(true)
                ->select('r.*, s.title as service_title, u.name as reviewer_name')
                ->from('#__services_reviews AS r')
                ->leftJoin('#__services AS s ON s.id = r.service_id')
                ->leftJoin('#__users AS u ON u.id = r.user_id')
                ->where('r.state >= 0')
                ->order('r.created DESC')
                ->setLimit($limit);
            
            $db->setQuery($query);
            return $db->loadObjectList();
            
        } catch (Exception $e) {
            return array();
        }
    }
    
    /**
     * Get pending reviews for dashboard
     *
     * @return  array  Array of pending reviews
     * @since   1.0.0
     */
    public static function getPendingReviews()
    {
        $db = Factory::getDbo();
        
        try {
            $query = $db->getQuery(true)
                ->select('r.*, s.title as service_title, u.name as reviewer_name')
                ->from('#__services_reviews AS r')
                ->leftJoin('#__services AS s ON s.id = r.service_id')
                ->leftJoin('#__users AS u ON u.id = r.user_id')
                ->where('r.state = 0')
                ->order('r.created ASC');
            
            $db->setQuery($query);
            return $db->loadObjectList();
            
        } catch (Exception $e) {
            return array();
        }
    }
    
    /**
     * Install sample data
     *
     * @return  boolean  True on success
     * @since   1.0.0
     */
    public static function installSampleData()
    {
        $db = Factory::getDbo();
        
        try {
            // Insert sample categories
            $categories = array(
                array('title' => 'Plumbing', 'alias' => 'plumbing', 'description' => 'Plumbing services and repairs'),
                array('title' => 'Electrical', 'alias' => 'electrical', 'description' => 'Electrical installation and maintenance'),
                array('title' => 'Construction', 'alias' => 'construction', 'description' => 'Construction and building services'),
                array('title' => 'Cleaning', 'alias' => 'cleaning', 'description' => 'Professional cleaning services'),
                array('title' => 'Landscaping', 'alias' => 'landscaping', 'description' => 'Garden and landscape services')
            );
            
            foreach ($categories as $category) {
                $query = $db->getQuery(true)
                    ->insert('#__services_categories')
                    ->columns($db->quoteName(array('title', 'alias', 'description', 'state', 'created')))
                    ->values(implode(',', array(
                        $db->quote($category['title']),
                        $db->quote($category['alias']),
                        $db->quote($category['description']),
                        1,
                        $db->quote(Factory::getDate()->toSql())
                    )));
                
                $db->setQuery($query);
                $db->execute();
            }
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
}