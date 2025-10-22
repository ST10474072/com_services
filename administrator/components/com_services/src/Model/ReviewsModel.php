<?php
namespace Jbaylet\Component\Services\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Reviews list model for the Services component.
 */
class ReviewsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'service_id', 's.id',
                'service_title', 's.title',
                'business_name', 'bu.name',
                'average_rating',
                'total_reviews',
                'pending_reviews',
                'latest_review_date',
                'business_created', 's.created'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   Fields to order by
     * @param   string  $direction  Direction of ordering
     *
     * @return  void
     */
    protected function populateState($ordering = 'latest_review_date', $direction = 'DESC')
    {
        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $ratingFilter = $this->getUserStateFromRequest($this->context . '.filter.rating', 'filter_rating', '', 'cmd');
        $this->setState('filter.rating', $ratingFilter);

        $reviewStatus = $this->getUserStateFromRequest($this->context . '.filter.review_status', 'filter_review_status', '', 'cmd');
        $this->setState('filter.review_status', $reviewStatus);

        parent::populateState($ordering, $direction);
    }

    /**
     * Get the query to retrieve data (businesses with review statistics)
     *
     * @return  \Joomla\Database\DatabaseQuery
     */
    protected function getListQuery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Check if tables exist
        $tables = $db->getTableList();
        $tablePrefix = $db->getPrefix();
        $servicesTable = $tablePrefix . 'services_items';
        $reviewsTable = $tablePrefix . 'services_reviews';
        
        if (!in_array($servicesTable, $tables)) {
            // Return empty query if tables don't exist
            $query->select('0 AS service_id, "No Businesses Found" AS service_title, "System" AS business_name, 0 AS business_user_id, 0.0 AS average_rating, 0 AS total_reviews, 0 AS pending_reviews, NOW() AS latest_review_date, NOW() AS business_created')
                  ->from('(SELECT 1) AS dummy')
                  ->where('1 = 0');
            return $query;
        }

        // Get businesses with review statistics
        $query->select([
            's.id AS service_id',
            's.title AS service_title',
            's.created AS business_created',
            's.created_by AS business_user_id',
            's.location AS business_location',
            's.logo AS business_logo'
        ]);
        
        // Review statistics
        $query->select('COUNT(r.id) AS total_reviews');
        $query->select('COALESCE(AVG(r.rating), 0) AS average_rating');
        $query->select('MAX(r.created) AS latest_review_date');
        $query->select('SUM(CASE WHEN r.state = 0 THEN 1 ELSE 0 END) AS pending_reviews');
        $query->select('SUM(CASE WHEN r.state = 1 THEN 1 ELSE 0 END) AS approved_reviews');
        
        // From services table (all services, even without reviews)
        $query->from($db->quoteName('#__services_items', 's'));

        // Left join with reviews to get statistics
        $query->leftJoin($db->quoteName('#__services_reviews', 'r') . ' ON ' . $db->quoteName('r.service_id') . ' = ' . $db->quoteName('s.id'));

        // Join with business user info
        $query->select('bu.name AS business_name, bu.email AS business_email')
              ->leftJoin($db->quoteName('#__users', 'bu') . ' ON ' . $db->quoteName('bu.id') . ' = ' . $db->quoteName('s.created_by'));

        // Group by service to get unique businesses (including user info)
        $query->group('s.id, s.title, s.created, s.created_by, s.location, s.logo, bu.name, bu.email');

        // Add search filter
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true)) . '%');
            $query->where('(s.title LIKE ' . $search . 
                         ' OR bu.name LIKE ' . $search . 
                         ' OR s.location LIKE ' . $search . 
                         ' OR r.comment LIKE ' . $search . ')');
        }

        // Filter by rating
        $ratingFilter = $this->getState('filter.rating');
        if (!empty($ratingFilter)) {
            switch ($ratingFilter) {
                case '5':
                    $query->having('average_rating >= 4.5');
                    break;
                case '4':
                    $query->having('average_rating >= 3.5 AND average_rating < 4.5');
                    break;
                case '3':
                    $query->having('average_rating >= 2.5 AND average_rating < 3.5');
                    break;
                case '2':
                    $query->having('average_rating >= 1.5 AND average_rating < 2.5');
                    break;
                case '1':
                    $query->having('average_rating >= 0.5 AND average_rating < 1.5');
                    break;
                case 'no_rating':
                    $query->having('average_rating = 0');
                    break;
            }
        }

        // Filter by review status
        $reviewStatus = $this->getState('filter.review_status');
        if ($reviewStatus === 'has_reviews') {
            $query->having('total_reviews > 0');
        } elseif ($reviewStatus === 'no_reviews') {
            $query->having('total_reviews = 0');
        } elseif ($reviewStatus === 'has_pending') {
            $query->having('pending_reviews > 0');
        }

        // Add the list ordering clause
        $orderCol = $this->state->get('list.ordering', 'latest_review_date');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        
        // Handle ordering with proper aggregate function references
        if ($orderCol === 'latest_review_date') {
            if ($orderDirn === 'DESC') {
                $query->order('ISNULL(MAX(r.created)), MAX(r.created) DESC');
            } else {
                $query->order('ISNULL(MAX(r.created)) DESC, MAX(r.created) ASC');
            }
        } elseif ($orderCol === 'average_rating') {
            $query->order('COALESCE(AVG(r.rating), 0) ' . $orderDirn);
        } elseif ($orderCol === 'total_reviews') {
            $query->order('COUNT(r.id) ' . $orderDirn);
        } elseif ($orderCol === 'pending_reviews') {
            $query->order('SUM(CASE WHEN r.state = 0 THEN 1 ELSE 0 END) ' . $orderDirn);
        } else {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     */
    public function getItems()
    {
        try {
            $items = parent::getItems();
            return $items;
        } catch (\Exception $e) {
            // Return empty array if there's an error (like table doesn't exist)
            return array();
        }
    }

    /**
     * Method to get the filter form.
     *
     * @param   array    $data      data
     * @param   boolean  $loadData  load current data
     *
     * @return  \Joomla\CMS\Form\Form|null  The Form object or null on error
     */
    public function getFilterForm($data = array(), $loadData = true)
    {
        try {
            // Load the form from the XML file
            $form = $this->loadForm(
                'com_services.reviews.filter',
                'filter_reviews',
                array(
                    'control' => 'filter',
                    'load_data' => $loadData
                )
            );
            
            if ($form) {
                // Populate current filter values
                if ($loadData) {
                    $data = array(
                        'filter' => array(
                            'search' => $this->getState('filter.search'),
                            'rating' => $this->getState('filter.rating'),
                            'review_status' => $this->getState('filter.review_status'),
                        ),
                        'list' => array(
                            'fullordering' => $this->getState('list.fullordering'),
                            'limit' => $this->getState('list.limit'),
                        )
                    );
                    $form->bind($data);
                }
                
                return $form;
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            Factory::getApplication()->enqueueMessage('Filter form error: ' . $e->getMessage(), 'warning');
        }
        
        // Return null instead of false to prevent getGroup() errors
        return null;
    }

    /**
     * Method to get an array of active filters
     *
     * @return  array  An array of active filters
     */
    public function getActiveFilters()
    {
        $activeFilters = array();

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $activeFilters['search'] = $search;
        }

        $ratingFilter = $this->getState('filter.rating');
        if (!empty($ratingFilter)) {
            $activeFilters['rating'] = $ratingFilter;
        }

        $reviewStatus = $this->getState('filter.review_status');
        if (!empty($reviewStatus)) {
            $activeFilters['review_status'] = $reviewStatus;
        }

        return $activeFilters;
    }

    /**
     * Get all reviews for a specific business/service
     *
     * @param   int  $serviceId  The service ID
     *
     * @return  array|null  Array of reviews for the business
     */
    public function getBusinessReviews($serviceId)
    {
        if (empty($serviceId)) {
            return null;
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select all reviews for the business with user and service details
        $query->select([
            'r.id',
            'r.service_id',
            'r.user_id',
            'r.rating',
            'r.comment',
            'r.helpful',
            'r.created',
            'r.state'
        ]);

        $query->from($db->quoteName('#__services_reviews', 'r'));

        // Join with services table for business info
        $query->select('s.title AS service_title, s.created_by AS business_user_id')
              ->leftJoin($db->quoteName('#__services_items', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('r.service_id'));

        // Join with business user info
        $query->select('bu.name AS business_name, bu.email AS business_email')
              ->leftJoin($db->quoteName('#__users', 'bu') . ' ON ' . $db->quoteName('bu.id') . ' = ' . $db->quoteName('s.created_by'));

        // Join with reviewer user info
        $query->select('u.name AS reviewer_name, u.email AS reviewer_email')
              ->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('r.user_id'));

        // Filter by service ID
        $query->where($db->quoteName('r.service_id') . ' = ' . (int) $serviceId);

        // Order by creation date (newest first)
        $query->order('r.created DESC');

        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return null;
        }
    }

    /**
     * Get business information by service ID
     *
     * @param   int  $serviceId  The service ID
     *
     * @return  object|null  Business information
     */
    public function getBusinessInfo($serviceId)
    {
        if (empty($serviceId)) {
            return null;
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
            's.id AS service_id',
            's.title AS service_title',
            's.location AS business_location',
            's.logo AS business_logo',
            's.created AS business_created',
            's.created_by AS business_user_id'
        ]);

        $query->from($db->quoteName('#__services_items', 's'));

        // Join with business user info
        $query->select('bu.name AS business_name, bu.email AS business_email')
              ->leftJoin($db->quoteName('#__users', 'bu') . ' ON ' . $db->quoteName('bu.id') . ' = ' . $db->quoteName('s.created_by'));

        $query->where($db->quoteName('s.id') . ' = ' . (int) $serviceId);

        $db->setQuery($query);

        try {
            return $db->loadObject();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return null;
        }
    }

    /**
     * Method to approve one or more reviews.
     *
     * @param   array  $pks  An array of review primary keys to approve.
     *
     * @return  boolean  True on success.
     */
    public function approve(&$pks)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__services_reviews'))
            ->set($db->quoteName('state') . ' = 1')
            ->where($db->quoteName('id') . ' IN (' . implode(',', array_map('intval', $pks)) . ')');
        
        $db->setQuery($query);
        
        try {
            $db->execute();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Method to reject one or more reviews.
     *
     * @param   array  $pks  An array of review primary keys to reject.
     *
     * @return  boolean  True on success.
     */
    public function reject(&$pks)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__services_reviews'))
            ->set($db->quoteName('state') . ' = 0')
            ->where($db->quoteName('id') . ' IN (' . implode(',', array_map('intval', $pks)) . ')');
        
        $db->setQuery($query);
        
        try {
            $db->execute();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }
}
