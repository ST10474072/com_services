<?php
namespace Jbaylet\Component\Services\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel as BaseItemModel;
use Joomla\CMS\Language\Text;
use Jbaylet\Component\Services\Site\Model\IntegratedModel;

/**
 * Item model for the Services component
 */
class ItemModel extends BaseItemModel
{
    /**
     * Model context string.
     *
     * @var  string
     */
    protected $_context = 'com_services.item';

    /**
     * Method to auto-populate the model state.
     *
     * @return  void
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load state from the request.
        $pk = $app->input->getInt('id');
        $this->setState('item.id', $pk);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        parent::populateState();
    }

    /**
     * Method to get an object with all integrated data.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  object|boolean  Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('item.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk])) {
            try {
                // Use integrated model for complete service data
                $integratedModel = new IntegratedModel();
                $user = Factory::getUser();
                $userId = $user->guest ? null : $user->id;
                
                $data = $integratedModel->getServiceDetails($pk, $userId);

                if (empty($data)) {
                    throw new \Exception(Text::_('COM_SERVICES_ERROR_ITEM_NOT_FOUND'), 404);
                }

                $this->_item[$pk] = $data;
            } catch (\Exception $e) {
                if ($e->getCode() == 404) {
                    // Need to go thru the error handler to allow Redirect to work.
                    throw $e;
                } else {
                    $this->setError($e);
                    $this->_item[$pk] = false;
                }
            }
        }

        return $this->_item[$pk];
    }

    /**
     * Get reviews for the current item
     *
     * @return  array
     */
    public function getReviews()
    {
        $pk = (int) $this->getState('item.id');
        
        if (!$pk) {
            return array();
        }

        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true)
                ->select('r.*, u.name as reviewer_name')
                ->from('#__services_reviews AS r')
                ->leftJoin('#__users AS u ON u.id = r.user_id')
                ->where('r.service_id = ' . $pk)
                ->where('r.state = 1')
                ->order('r.created DESC')
                ->setLimit(20);

            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return array();
        }
    }

    /**
     * Get related services
     *
     * @return  array
     */
    public function getRelatedServices()
    {
        $item = $this->getItem();
        
        if (!$item) {
            return array();
        }

        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true)
                ->select('a.id, a.title, a.alias, a.logo, a.location, a.rating_avg, a.reviews_count')
                ->from('#__services_items AS a')
                ->where('a.id != ' . (int) $item->id)
                ->where('a.state = 1')
                ->order('a.rating_avg DESC, a.created DESC')
                ->setLimit(6);

            // Add location-based filtering if available
            if (!empty($item->location)) {
                $location = $db->quote('%' . $db->escape($item->location, true) . '%');
                $query->where('a.location LIKE ' . $location);
            }

            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return array();
        }
    }
}