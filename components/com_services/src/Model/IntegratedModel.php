<?php
namespace Jbaylet\Component\Services\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\User;

/**
 * Integrated model for handling all services relationships
 */
class IntegratedModel extends BaseDatabaseModel
{
    /**
     * Get service details with all related information
     *
     * @param   int  $serviceId  The service ID
     * @param   int  $userId     Optional user ID for user-specific data
     *
     * @return  object|null  Service data with relationships
     */
    public function getServiceDetails($serviceId, $userId = null)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Main service data with category and business owner info
        $query->select([
            's.id', 's.title', 's.alias', 's.description', 's.logo', 's.location',
            's.lat', 's.lng', 's.is_247', 's.is_emergency', 's.rating_avg', 's.reviews_count',
            's.is_featured', 's.created', 's.state', 's.category_id'
        ])
        ->from($db->quoteName('#__services_items', 's'))
        ->where($db->quoteName('s.id') . ' = ' . (int) $serviceId)
        ->where($db->quoteName('s.state') . ' = 1');

        // Join with category
        $query->select('c.title AS category_title, c.alias AS category_alias')
              ->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = s.category_id');

        // Join with business owner (created_by user)
        $query->select([
            'owner.id AS owner_id', 'owner.name AS owner_name', 'owner.email AS owner_email'
        ])
        ->leftJoin($db->quoteName('#__users', 'owner') . ' ON owner.id = s.created_by');

        // Join with business profile
        $query->select([
            'p.business_name', 'p.phone AS business_phone', 'p.email AS business_email',
            'p.whatsapp', 'p.website', 'p.about AS business_about', 'p.address AS business_address',
            'p.logo AS business_logo'
        ])
        ->leftJoin($db->quoteName('#__services_profiles', 'p') . ' ON p.user_id = s.created_by');

        $db->setQuery($query);

        try {
            $service = $db->loadObject();
            
            if (!$service) {
                return null;
            }

            // Add related data
            $service->reviews = $this->getServiceReviews($serviceId, 5); // Latest 5 reviews
            $service->review_stats = $this->getReviewStats($serviceId);
            
            if ($userId) {
                $service->user_review = $this->getUserReviewForService($serviceId, $userId);
                $service->can_message = $this->canUserMessageService($serviceId, $userId);
                $service->message_thread = $this->getMessageThread($serviceId, $userId);
            }

            return $service;
            
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage('Error loading service: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get reviews for a service
     *
     * @param   int  $serviceId  Service ID
     * @param   int  $limit      Number of reviews to return
     *
     * @return  array  Reviews data
     */
    public function getServiceReviews($serviceId, $limit = 10)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
            'r.id', 'r.rating', 'r.comment', 'r.helpful', 'r.created', 'r.state',
            'u.name AS reviewer_name'
        ])
        ->from($db->quoteName('#__services_reviews', 'r'))
        ->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = r.user_id')
        ->where($db->quoteName('r.service_id') . ' = ' . (int) $serviceId)
        ->where($db->quoteName('r.state') . ' = 1')
        ->order('r.created DESC');

        if ($limit > 0) {
            $query->setLimit($limit);
        }

        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (\Exception $e) {
            return array();
        }
    }

    /**
     * Get review statistics for a service
     *
     * @param   int  $serviceId  Service ID
     *
     * @return  object  Review statistics
     */
    public function getReviewStats($serviceId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
            'COUNT(*) AS total_reviews',
            'AVG(rating) AS average_rating',
            'SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) AS five_star',
            'SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) AS four_star',
            'SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS three_star',
            'SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS two_star',
            'SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS one_star'
        ])
        ->from($db->quoteName('#__services_reviews'))
        ->where($db->quoteName('service_id') . ' = ' . (int) $serviceId)
        ->where($db->quoteName('state') . ' = 1');

        $db->setQuery($query);

        try {
            $stats = $db->loadObject();
            
            // Calculate percentages
            if ($stats->total_reviews > 0) {
                $stats->five_star_percent = round(($stats->five_star / $stats->total_reviews) * 100);
                $stats->four_star_percent = round(($stats->four_star / $stats->total_reviews) * 100);
                $stats->three_star_percent = round(($stats->three_star / $stats->total_reviews) * 100);
                $stats->two_star_percent = round(($stats->two_star / $stats->total_reviews) * 100);
                $stats->one_star_percent = round(($stats->one_star / $stats->total_reviews) * 100);
            } else {
                $stats->five_star_percent = 0;
                $stats->four_star_percent = 0;
                $stats->three_star_percent = 0;
                $stats->two_star_percent = 0;
                $stats->one_star_percent = 0;
            }

            return $stats;
            
        } catch (\Exception $e) {
            return (object) [
                'total_reviews' => 0,
                'average_rating' => 0,
                'five_star' => 0, 'four_star' => 0, 'three_star' => 0, 'two_star' => 0, 'one_star' => 0,
                'five_star_percent' => 0, 'four_star_percent' => 0, 'three_star_percent' => 0, 'two_star_percent' => 0, 'one_star_percent' => 0
            ];
        }
    }

    /**
     * Get user's review for a service
     *
     * @param   int  $serviceId  Service ID
     * @param   int  $userId     User ID
     *
     * @return  object|null  User's review
     */
    public function getUserReviewForService($serviceId, $userId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
              ->from($db->quoteName('#__services_reviews'))
              ->where($db->quoteName('service_id') . ' = ' . (int) $serviceId)
              ->where($db->quoteName('user_id') . ' = ' . (int) $userId);

        $db->setQuery($query);

        try {
            return $db->loadObject();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if user can message the service provider
     *
     * @param   int  $serviceId  Service ID
     * @param   int  $userId     User ID
     *
     * @return  bool  Whether user can message
     */
    public function canUserMessageService($serviceId, $userId)
    {
        if (!$userId) {
            return false;
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Check if user is not the service owner
        $query->select('created_by')
              ->from($db->quoteName('#__services_items'))
              ->where($db->quoteName('id') . ' = ' . (int) $serviceId);

        $db->setQuery($query);

        try {
            $ownerId = $db->loadResult();
            return $ownerId && $ownerId != $userId;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get message thread between user and service provider
     *
     * @param   int  $serviceId  Service ID
     * @param   int  $userId     User ID
     *
     * @return  array  Messages in thread
     */
    public function getMessageThread($serviceId, $userId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Get service owner first
        $ownerQuery = $db->getQuery(true)
            ->select('created_by')
            ->from($db->quoteName('#__services_items'))
            ->where($db->quoteName('id') . ' = ' . (int) $serviceId);
        
        $db->setQuery($ownerQuery);
        $ownerId = $db->loadResult();

        if (!$ownerId) {
            return array();
        }

        // Get messages between user and service owner for this service
        $query->select([
            'm.id', 'm.body', 'm.attachment', 'm.created', 'm.seen',
            'm.sender_id', 'm.receiver_id',
            'sender.name AS sender_name',
            'receiver.name AS receiver_name'
        ])
        ->from($db->quoteName('#__services_messages', 'm'))
        ->leftJoin($db->quoteName('#__users', 'sender') . ' ON sender.id = m.sender_id')
        ->leftJoin($db->quoteName('#__users', 'receiver') . ' ON receiver.id = m.receiver_id')
        ->where($db->quoteName('m.service_id') . ' = ' . (int) $serviceId)
        ->where('(' . 
            '(m.sender_id = ' . (int) $userId . ' AND m.receiver_id = ' . (int) $ownerId . ') OR ' .
            '(m.sender_id = ' . (int) $ownerId . ' AND m.receiver_id = ' . (int) $userId . ')' .
        ')')
        ->order('m.created ASC');

        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (\Exception $e) {
            return array();
        }
    }

    /**
     * Search services with integrated filters
     *
     * @param   array  $filters  Search filters
     *
     * @return  array  Search results with relationships
     */
    public function searchServices($filters = [])
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Main service data
        $query->select([
            's.id', 's.title', 's.alias', 's.description', 's.logo', 's.location',
            's.lat', 's.lng', 's.is_247', 's.is_emergency', 's.rating_avg', 's.reviews_count',
            's.is_featured', 's.created'
        ])
        ->from($db->quoteName('#__services_items', 's'))
        ->where($db->quoteName('s.state') . ' = 1');

        // Join category
        $query->select('c.title AS category_title')
              ->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = s.category_id');

        // Join business profile
        $query->select('p.business_name')
              ->leftJoin($db->quoteName('#__services_profiles', 'p') . ' ON p.user_id = s.created_by');

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $db->quote('%' . $db->escape($filters['search']) . '%');
            $query->where('(s.title LIKE ' . $search . ' OR s.description LIKE ' . $search . ' OR p.business_name LIKE ' . $search . ')');
        }

        if (!empty($filters['category_id'])) {
            $query->where('s.category_id = ' . (int) $filters['category_id']);
        }

        if (!empty($filters['location'])) {
            $location = $db->quote('%' . $db->escape($filters['location']) . '%');
            $query->where('(s.location LIKE ' . $location . ' OR p.address LIKE ' . $location . ')');
        }

        // Rating filter (minimum rating)
        if (!empty($filters['rating']) && is_numeric($filters['rating'])) {
            $minRating = (float) $filters['rating'];
            $query->where('s.rating_avg >= ' . $minRating);
        }
        
        // Legacy rating_min support
        if (!empty($filters['rating_min'])) {
            $query->where('s.rating_avg >= ' . (float) $filters['rating_min']);
        }

        if (!empty($filters['is_247'])) {
            $query->where('s.is_247 = 1');
        }

        if (!empty($filters['is_emergency'])) {
            $query->where('s.is_emergency = 1');
        }

        if (!empty($filters['is_featured'])) {
            $query->where('s.is_featured = 1');
        }
        
        // Radius filter (if location coordinates are available)
        if (!empty($filters['radius']) && !empty($filters['user_lat']) && !empty($filters['user_lng'])) {
            $radius = (int) $filters['radius'];
            $userLat = (float) $filters['user_lat'];
            $userLng = (float) $filters['user_lng'];
            
            // Using Haversine formula for distance calculation
            $query->having(
                '(6371 * ACOS(COS(RADIANS(' . $userLat . ')) * COS(RADIANS(s.lat)) * ' .
                'COS(RADIANS(s.lng) - RADIANS(' . $userLng . ')) + ' .
                'SIN(RADIANS(' . $userLat . ')) * SIN(RADIANS(s.lat)))) <= ' . $radius
            );
        }

        // Ordering - handle both order_by and sort filters
        $orderBy = $filters['order_by'] ?? $filters['sort'] ?? 'featured_first';
        
        // Map filter_sort values to ordering
        $sortMapping = [
            'created_desc' => 'newest',
            'title_asc' => 'title',
            'rating_desc' => 'rating',
            'reviews_desc' => 'reviews',
            'location_asc' => 'location'
        ];
        
        if (isset($sortMapping[$orderBy])) {
            $orderBy = $sortMapping[$orderBy];
        }
        
        switch ($orderBy) {
            case 'featured_first':
                $query->order('s.is_featured DESC, s.rating_avg DESC, s.created DESC');
                break;
            case 'rating':
                $query->order('s.rating_avg DESC, s.reviews_count DESC');
                break;
            case 'newest':
                $query->order('s.created DESC');
                break;
            case 'title':
                $query->order('s.title ASC');
                break;
            case 'reviews':
                $query->order('s.reviews_count DESC, s.rating_avg DESC');
                break;
            case 'location':
                $query->order('s.location ASC');
                break;
            default:
                $query->order('s.is_featured DESC, s.rating_avg DESC');
        }

        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage('Search error: ' . $e->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Get business dashboard data for service owner
     *
     * @param   int  $userId  Business owner user ID
     *
     * @return  object  Dashboard data
     */
    public function getBusinessDashboard($userId)
    {
        $db = Factory::getDbo();
        
        // Get business services
        $servicesQuery = $db->getQuery(true)
            ->select([
                's.id', 's.title', 's.rating_avg', 's.reviews_count', 's.is_featured', 's.state',
                'c.title AS category_title'
            ])
            ->from($db->quoteName('#__services_items', 's'))
            ->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = s.category_id')
            ->where($db->quoteName('s.created_by') . ' = ' . (int) $userId)
            ->order('s.created DESC');

        $db->setQuery($servicesQuery);
        $services = $db->loadObjectList();

        // Get unread messages count
        $messagesQuery = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__services_messages'))
            ->where($db->quoteName('receiver_id') . ' = ' . (int) $userId)
            ->where($db->quoteName('seen') . ' = 0');

        $db->setQuery($messagesQuery);
        $unreadMessages = (int) $db->loadResult();

        // Get recent reviews
        $reviewsQuery = $db->getQuery(true)
            ->select([
                'r.id', 'r.rating', 'r.comment', 'r.created',
                's.title AS service_title',
                'u.name AS reviewer_name'
            ])
            ->from($db->quoteName('#__services_reviews', 'r'))
            ->leftJoin($db->quoteName('#__services_items', 's') . ' ON s.id = r.service_id')
            ->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = r.user_id')
            ->where($db->quoteName('s.created_by') . ' = ' . (int) $userId)
            ->where($db->quoteName('r.state') . ' = 1')
            ->order('r.created DESC')
            ->setLimit(10);

        $db->setQuery($reviewsQuery);
        $recentReviews = $db->loadObjectList();

        return (object) [
            'services' => $services,
            'services_count' => count($services),
            'unread_messages' => $unreadMessages,
            'recent_reviews' => $recentReviews,
            'total_reviews' => array_sum(array_column($services, 'reviews_count')),
            'average_rating' => count($services) > 0 ? 
                array_sum(array_column($services, 'rating_avg')) / count($services) : 0
        ];
    }
}