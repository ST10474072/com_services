<?php
namespace Jbaylet\Component\Services\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Jbaylet\Component\Services\Site\Model\IntegratedModel;

class ItemsModel extends ListModel
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
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'category_id', 'a.category_id',
                'location', 'a.location',
                'rating_avg', 'a.rating_avg',
                'is_featured', 'a.is_featured',
                'created', 'a.created',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     */
    protected function populateState($ordering = 'a.created', $direction = 'DESC')
    {
        $app = Factory::getApplication();
        
        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);
        
        $category = $app->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '', 'int');
        $this->setState('filter.category_id', $category);
        
        $location = $app->getUserStateFromRequest($this->context . '.filter.location', 'filter_location', '', 'string');
        $this->setState('filter.location', $location);
        
        $radius = $app->getUserStateFromRequest($this->context . '.filter.radius', 'filter_radius', '', 'int');
        $this->setState('filter.radius', $radius);
        
        $rating = $app->getUserStateFromRequest($this->context . '.filter.rating', 'filter_rating', '', 'int');
        $this->setState('filter.rating', $rating);
        
        $sort = $app->getUserStateFromRequest($this->context . '.filter.sort', 'filter_sort', '', 'string');
        $this->setState('filter.sort', $sort);
        
        // Handle checkbox filters - hidden inputs ensure they always send values
        $featured = $app->getUserStateFromRequest($this->context . '.filter.featured', 'filter_featured', 0, 'int');
        $this->setState('filter.featured', $featured);
        
        $is247 = $app->getUserStateFromRequest($this->context . '.filter.247', 'filter_247', 0, 'int');
        $this->setState('filter.247', $is247);
        
        $emergency = $app->getUserStateFromRequest($this->context . '.filter.emergency', 'filter_emergency', 0, 'int');
        $this->setState('filter.emergency', $emergency);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_services');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
        
        // Set the limit from component parameters
        $limit = $params->get('items_limit', 20);
        $this->setState('list.limit', $limit);
    }

    protected function getListQuery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select statement
        $query->select(
            $this->getState(
                'list.select',
'a.id, a.title, a.alias, a.short_description, a.long_description, a.logo, a.location, a.rating_avg, a.reviews_count, a.is_247, a.is_emergency, a.is_featured, a.created'
            )
        )
        ->from($db->quoteName('#__services_items', 'a'))
        ->where('a.state = 1');

        // Filter by search in title or description
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true)) . '%');
$query->where('(a.title LIKE ' . $search . ' OR a.short_description LIKE ' . $search . ' OR a.long_description LIKE ' . $search . ' OR a.description LIKE ' . $search . ')');
        }

        // Filter by category
        $categoryId = $this->getState('filter.category_id');
        if (is_numeric($categoryId) && $categoryId > 0) {
            $query->where('a.category_id = ' . (int) $categoryId);
        }

        // Filter by location
        $location = $this->getState('filter.location');
        if (!empty($location)) {
            $location = $db->quote('%' . $db->escape($location, true) . '%');
            $query->where('a.location LIKE ' . $location);
        }

        // Filter by featured
        $featured = $this->getState('filter.featured');
        if ($featured == 1) {
            $query->where('a.is_featured = 1');
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering', 'a.created');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.location');
        $id .= ':' . $this->getState('filter.radius');
        $id .= ':' . $this->getState('filter.rating');
        $id .= ':' . $this->getState('filter.sort');
        $id .= ':' . $this->getState('filter.featured');
        $id .= ':' . $this->getState('filter.247');
        $id .= ':' . $this->getState('filter.emergency');

        return parent::getStoreId($id);
    }

    /**
     * Method to get available categories for the services component.
     *
     * @return  array  An array of categories.
     */
    public function getCategories()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('c.id, c.title, c.alias, c.description')
              ->from($db->quoteName('#__categories', 'c'))
              ->where($db->quoteName('c.extension') . ' = ' . $db->quote('com_services'))
              ->where($db->quoteName('c.published') . ' = 1')
              ->where($db->quoteName('c.level') . ' > 0') // Exclude root category
              ->order('c.lft ASC'); // Order by nested set left value

        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage('Error loading categories: ' . $e->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Get items using integrated search functionality
     *
     * @return  array  Array of services with integrated data
     */
    public function getItems()
    {
        try {
            // Use integrated model for comprehensive search
            $integratedModel = new IntegratedModel();
            
            $filters = [
                'search' => $this->getState('filter.search'),
                'category_id' => $this->getState('filter.category_id'),
                'location' => $this->getState('filter.location'),
                'radius' => $this->getState('filter.radius'),
                'rating' => $this->getState('filter.rating'),
                'rating_min' => $this->getState('filter.rating'), // Map rating to rating_min for compatibility
                'sort' => $this->getState('filter.sort'),
                'is_247' => $this->getState('filter.247'),
                'is_emergency' => $this->getState('filter.emergency'),
                'is_featured' => $this->getState('filter.featured'),
                'order_by' => $this->getState('list.ordering')
            ];
            
            // Remove empty filters
            $filters = array_filter($filters, function($value) {
                return $value !== '' && $value !== null;
            });
            
            $results = $integratedModel->searchServices($filters);
            
            // If integrated model returns empty but no error, try fallback
            if (empty($results) && !Factory::getApplication()->getMessageQueue()) {
                Factory::getApplication()->enqueueMessage('Using fallback search method', 'info');
                return $this->getFallbackItems();
            }
            
            return $results;
            
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage('Error with integrated search, using fallback: ' . $e->getMessage(), 'warning');
            return $this->getFallbackItems();
        }
    }
    
    /**
     * Fallback method to get items using parent ListModel functionality
     *
     * @return  array  Array of services
     */
    protected function getFallbackItems()
    {
        try {
            // Use parent's getItems which uses getListQuery
            $query = $this->getListQuery();
            return $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage('Fallback search also failed: ' . $e->getMessage(), 'error');
            return array();
        }
    }
}
