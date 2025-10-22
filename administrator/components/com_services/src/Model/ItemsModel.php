<?php
namespace Jbaylet\Component\Services\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Exception;

/**
 * Items list model for the Services component.
 */
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
                'location', 'a.location',
                'state', 'a.state',
                'featured', 'a.is_featured',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'category_id', 'a.category_id',
                'lat', 'a.lat',
                'lng', 'a.lng',
                'logo', 'a.logo'
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
    protected function populateState($ordering = 'a.created', $direction = 'DESC')
    {
        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd');
        $this->setState('filter.state', $state);

        $featured = $this->getUserStateFromRequest($this->context . '.filter.featured', 'filter_featured', '', 'cmd');
        $this->setState('filter.featured', $featured);

        $categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '', 'int');
        $this->setState('filter.category_id', $categoryId);

        parent::populateState($ordering, $direction);
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
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.featured');
        $id .= ':' . $this->getState('filter.category_id');

        return parent::getStoreId($id);
    }

    /**
     * Get the query to retrieve data
     *
     * @return  \Joomla\Database\DatabaseQuery
     */
    protected function getListQuery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Robust check if the table exists (avoids driver-specific formats from getTableList)
        $exists = true;
        try {
            $like = $db->quote($db->getPrefix() . 'services_items');
            $db->setQuery('SHOW TABLES LIKE ' . $like);
            $exists = (bool) $db->loadResult();
        } catch (\RuntimeException $e) {
            // If the check itself fails, assume it exists to avoid false negatives; getItems() is guarded.
            $exists = true;
        }

        if (!$exists) {
            // Return empty query if table doesn't exist (prevents SQL errors in list view)
            $query->select('1 AS id, "No Services Found" AS title, "Tables not created yet" AS location, 1 AS state, 0 AS is_featured, NOW() AS created')
                  ->from('(SELECT 1) AS dummy')
                  ->where('1 = 0'); // This ensures no results are returned
            return $query;
        }

        // Select fields
        $query->select(
            $this->getState(
'list.select',
                'a.id, a.title, a.alias, a.category_id, a.logo, a.location, a.lat, a.lng, a.state, a.is_featured, a.created, a.rating_avg, a.reviews_count'
            )
        );

        // From table
        $query->from($db->quoteName('#__services_items', 'a'));

        // Join with users for created by
        $query->select('u.name AS author')
              ->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'));

        // Join with categories for display
        $query->select('c.title AS category_title')
              ->leftJoin($db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.category_id') . ' AND ' . $db->quoteName('c.extension') . " = 'com_services'");

        // Filter by search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true)) . '%');
            $query->where('(a.title LIKE ' . $search . ' OR a.location LIKE ' . $search . ')');
        }

        // Filter by state
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.state = ' . (int) $state);
        }

        // Filter by featured
        $featured = $this->getState('filter.featured');
        if ($featured == '1') {
            $query->where('a.is_featured = 1');
        } elseif ($featured == '0') {
            $query->where('a.is_featured = 0');
        }

        // Filter by category
        $categoryId = (int) $this->getState('filter.category_id');
        if ($categoryId > 0) {
            $query->where('a.category_id = ' . (int) $categoryId);
        }

        // Add the list ordering clause
        $orderCol = $this->state->get('list.ordering', 'a.created');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        
        if ($orderCol && $orderDirn) {
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
     * @return  \Joomla\CMS\Form\Form|false  The Form object or false on error
     */
    public function getFilterForm($data = array(), $loadData = true)
    {
        try {
            $form = $this->loadForm('com_services.filter_items', 'filter_items', array('control' => '', 'load_data' => $loadData));
            
            if ($form) {
                return $form;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
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

        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $activeFilters['state'] = $state;
        }

        $featured = $this->getState('filter.featured');
        if ($featured !== '') {
            $activeFilters['featured'] = $featured;
        }

        return $activeFilters;
    }
}