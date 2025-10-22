<?php
namespace Jbaylet\Component\Services\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Messages list model for the Services component.
 */
class MessagesModel extends ListModel
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
                'thread_id', 'a.thread_id',
                'service_id', 'a.service_id',
                'last_message_date',
                'message_count',
                'unread_count',
                'service_title', 's.title',
                'business_name', 'bu.name',
                'sender_name', 'u.name',
                'receiver_name', 'ur.name'
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
    protected function populateState($ordering = 'last_message_date', $direction = 'DESC')
    {
        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $threadStatus = $this->getUserStateFromRequest($this->context . '.filter.thread_status', 'filter_thread_status', '', 'cmd');
        $this->setState('filter.thread_status', $threadStatus);

        $messageCount = $this->getUserStateFromRequest($this->context . '.filter.message_count', 'filter_message_count', '', 'cmd');
        $this->setState('filter.message_count', $messageCount);

        parent::populateState($ordering, $direction);
    }

    /**
     * Get the query to retrieve data (chat threads)
     *
     * @return  \Joomla\Database\DatabaseQuery
     */
    protected function getListQuery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Check if table exists
        $tables = $db->getTableList();
        $tablePrefix = $db->getPrefix();
        $tableName = $tablePrefix . 'services_messages';
        
        if (!in_array($tableName, $tables)) {
            // Return empty query if table doesn't exist
            $query->select('"thread-0" AS thread_id, 0 AS service_id, "No chats available yet" AS service_title, "System" AS sender_name, "System" AS receiver_name, 1 AS message_count, 0 AS unread_count, NOW() AS last_message_date, "No messages" AS last_message_preview')
                  ->from('(SELECT 1) AS dummy')
                  ->where('1 = 0');
            return $query;
        }

        // Get chat threads grouped by thread_id with latest message info
        $query->select([
            'a.thread_id',
            'a.service_id',
            'MAX(a.created) AS last_message_date',
            'COUNT(a.id) AS message_count',
            'SUM(CASE WHEN a.seen = 0 THEN 1 ELSE 0 END) AS unread_count',
            'a.sender_id',
            'a.receiver_id'
        ]);
        
        // Subquery to get the latest message content for preview
        $subQuery = $db->getQuery(true)
            ->select('m2.body')
            ->from($db->quoteName('#__services_messages', 'm2'))
            ->where('m2.thread_id = a.thread_id')
            ->order('m2.created DESC')
            ->setLimit(1);
        $query->select('(' . $subQuery . ') AS last_message_preview');
        
        // From table
        $query->from($db->quoteName('#__services_messages', 'a'));

        // Join with services table for business info
        $query->select(['s.title AS service_title', 's.created_by AS business_user_id'])
              ->leftJoin($db->quoteName('#__services_items', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('a.service_id'));

        // Join with business user info
        $query->select('bu.name AS business_name, bu.email AS business_email')
              ->leftJoin($db->quoteName('#__users', 'bu') . ' ON ' . $db->quoteName('bu.id') . ' = ' . $db->quoteName('s.created_by'));

        // Join with sender user info
        $query->select('u.name AS sender_name, u.email AS sender_email')
              ->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.sender_id'));
              
        // Join with receiver user info
        $query->select('ur.name AS receiver_name, ur.email AS receiver_email')
              ->leftJoin($db->quoteName('#__users', 'ur') . ' ON ' . $db->quoteName('ur.id') . ' = ' . $db->quoteName('a.receiver_id'));

        // Group by thread to get unique conversations
        $query->group('a.thread_id, a.service_id, a.sender_id, a.receiver_id');

        // Add search filter
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true)) . '%');
            $query->where('(s.title LIKE ' . $search . 
                         ' OR bu.name LIKE ' . $search . 
                         ' OR u.name LIKE ' . $search . 
                         ' OR ur.name LIKE ' . $search . 
                         ' OR a.thread_id LIKE ' . $search . 
                         ' OR a.body LIKE ' . $search . ')');
        }

        // Filter by thread status (read/unread)
        $threadStatus = $this->getState('filter.thread_status');
        if ($threadStatus === 'read') {
            $query->having('unread_count = 0');
        } elseif ($threadStatus === 'unread') {
            $query->having('unread_count > 0');
        }

        // Filter by message count
        $messageCount = $this->getState('filter.message_count');
        if ($messageCount === '1') {
            $query->having('message_count = 1');
        } elseif ($messageCount === '2-5') {
            $query->having('message_count BETWEEN 2 AND 5');
        } elseif ($messageCount === '6+') {
            $query->having('message_count >= 6');
        }

        // Add the list ordering clause
        $orderCol = $this->state->get('list.ordering', 'last_message_date');
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
            $form = $this->loadForm('com_services.filter_messages', 'filter_messages', array('control' => '', 'load_data' => $loadData));
            
            if ($form) {
                return $form;
            }
        } catch (\Exception $e) {
            // If form loading fails, create a basic search form
        }
        
        // Return a basic form if the specific form doesn't exist
        try {
            $form = $this->loadForm('com_services.filter_basic', 'filter_basic', array('control' => '', 'load_data' => $loadData));
            return $form;
        } catch (\Exception $e) {
            // Return null instead of false to prevent getGroup() errors
            return null;
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

        $threadStatus = $this->getState('filter.thread_status');
        if (!empty($threadStatus)) {
            $activeFilters['thread_status'] = $threadStatus;
        }

        $messageCount = $this->getState('filter.message_count');
        if (!empty($messageCount)) {
            $activeFilters['message_count'] = $messageCount;
        }

        return $activeFilters;
    }

    /**
     * Get full conversation for a specific thread
     *
     * @param   string  $threadId  The thread ID
     *
     * @return  array|null  Array of messages in the conversation
     */
    public function getChatConversation($threadId)
    {
        if (empty($threadId)) {
            return null;
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select all messages in the thread with user details
        $query->select([
            'a.id',
            'a.thread_id',
            'a.service_id',
            'a.sender_id',
            'a.receiver_id',
            'a.body',
            'a.attachment',
            'a.seen',
            'a.created'
        ]);

        $query->from($db->quoteName('#__services_messages', 'a'));

        // Join with services table
        $query->select('s.title AS service_title, s.created_by AS business_user_id')
              ->leftJoin($db->quoteName('#__services_items', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('a.service_id'));

        // Join with sender user info
        $query->select('u.name AS sender_name, u.email AS sender_email')
              ->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.sender_id'));

        // Join with receiver user info
        $query->select('ur.name AS receiver_name, ur.email AS receiver_email')
              ->leftJoin($db->quoteName('#__users', 'ur') . ' ON ' . $db->quoteName('ur.id') . ' = ' . $db->quoteName('a.receiver_id'));

        // Filter by thread ID
        $query->where($db->quoteName('a.thread_id') . ' = ' . $db->quote($threadId));

        // Order by creation date
        $query->order('a.created ASC');

        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return null;
        }
    }

    /**
     * Mark all messages in a thread as seen
     *
     * @param   string  $threadId  The thread ID
     *
     * @return  boolean  True on success
     */
    public function markThreadAsSeen($threadId)
    {
        if (empty($threadId)) {
            return false;
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__services_messages'))
            ->set($db->quoteName('seen') . ' = 1')
            ->where($db->quoteName('thread_id') . ' = ' . $db->quote($threadId));

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
     * Delete entire chat thread
     *
     * @param   string  $threadId  The thread ID
     *
     * @return  boolean  True on success
     */
    public function deleteThread($threadId)
    {
        if (empty($threadId)) {
            return false;
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__services_messages'))
            ->where($db->quoteName('thread_id') . ' = ' . $db->quote($threadId));

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
