<?php
namespace Jbaylet\Component\Services\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;

// Include database helper with proper error handling
$helperPath = JPATH_ADMINISTRATOR . '/components/com_services/helpers/database.php';
if (file_exists($helperPath)) {
    require_once $helperPath;
}

// Ensure the class is available or create a fallback
if (!class_exists('ServicesHelperDatabase')) {
    class ServicesHelperDatabase {
        public static function checkTables() { return false; }
        public static function createTables() { return true; }
        public static function getStats() {
            $stats = new \stdClass();
            $stats->totalServices = 0;
            $stats->totalReviews = 0;
            $stats->totalMessages = 0;
            $stats->featuredServices = 0;
            $stats->servicesThisMonth = 0;
            $stats->averageRating = 0.0;
            return $stats;
        }
        public static function getRecentServices($limit = 5) { return array(); }
        public static function getRecentReviews($limit = 5) { return array(); }
        public static function getPendingReviews() { return array(); }
    }
}

/**
 * Dashboard model for the Services component.
 */
class DashboardModel extends BaseModel
{
    /**
     * Get dashboard statistics
     *
     * @return  object
     */
    public function getStats()
    {
        // Check if database tables exist, create if needed
        if (!\ServicesHelperDatabase::checkTables()) {
            \ServicesHelperDatabase::createTables();
        }
        
        return \ServicesHelperDatabase::getStats();
    }

    /**
     * Get recent services
     *
     * @param   int  $limit  Number of services to fetch
     *
     * @return  array
     */
    public function getRecentServices($limit = 10)
    {
        return \ServicesHelperDatabase::getRecentServices($limit);
    }

    /**
     * Get recent reviews
     *
     * @param   int  $limit  Number of reviews to fetch
     *
     * @return  array
     */
    public function getRecentReviews($limit = 10)
    {
        return \ServicesHelperDatabase::getRecentReviews($limit);
    }

    /**
     * Get services by category for chart
     *
     * @return  array
     */
    public function getCategoryStats()
    {
        $db = Factory::getDbo();

        try {
            $query = $db->getQuery(true)
                ->select('category_id, COUNT(*) as count')
                ->from('#__services_items')
                ->where('state = 1')
                ->group('category_id')
                ->order('count DESC')
                ->setLimit(10);

            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return array();
        }
    }

    /**
     * Get pending reviews that need moderation
     *
     * @return  array
     */
    public function getPendingReviews()
    {
        return \ServicesHelperDatabase::getPendingReviews();
    }
}