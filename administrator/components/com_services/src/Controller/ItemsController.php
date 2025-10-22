<?php
namespace Jbaylet\Component\Services\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * Items list controller class.
 */
class ItemsController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_SERVICES_ITEMS';

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     */
    public function getModel($name = 'Item', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to publish a list of items
     *
     * @return  void
     */
    public function publish()
    {
        // Check for request forgeries
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Get items to publish from the request
        $cid = $this->input->get('cid', array(), 'array');

        if (!is_array($cid) || count($cid) < 1) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_NO_ITEMS_SELECTED'), 'warning');
        } else {
            // Get the model
            $model = $this->getModel();

            // Publish the items
            if (!$model->publish($cid, 1)) {
                Factory::getApplication()->enqueueMessage($model->getError(), 'error');
            } else {
                $ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
                Factory::getApplication()->enqueueMessage(Text::plural($ntext, count($cid)), 'message');
            }
        }

        $this->setRedirect('index.php?option=com_services&view=items');
    }

    /**
     * Method to unpublish a list of items
     *
     * @return  void
     */
    public function unpublish()
    {
        // Check for request forgeries
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Get items to unpublish from the request
        $cid = $this->input->get('cid', array(), 'array');

        if (!is_array($cid) || count($cid) < 1) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_NO_ITEMS_SELECTED'), 'warning');
        } else {
            // Get the model
            $model = $this->getModel();

            // Unpublish the items
            if (!$model->publish($cid, 0)) {
                Factory::getApplication()->enqueueMessage($model->getError(), 'error');
            } else {
                $ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
                Factory::getApplication()->enqueueMessage(Text::plural($ntext, count($cid)), 'message');
            }
        }

        $this->setRedirect('index.php?option=com_services&view=items');
    }

    /**
     * Method to feature a list of items
     *
     * @return  void
     */
    public function feature()
    {
        // Check for request forgeries
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Get items to feature from the request
        $cid = $this->input->get('cid', array(), 'array');

        if (!is_array($cid) || count($cid) < 1) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_NO_ITEMS_SELECTED'), 'warning');
        } else {
            // Get the model
            $model = $this->getModel();

            // Feature the items
            if (!$model->feature($cid, 1)) {
                Factory::getApplication()->enqueueMessage($model->getError(), 'error');
            } else {
                $ntext = $this->text_prefix . '_N_ITEMS_FEATURED';
                Factory::getApplication()->enqueueMessage(Text::plural($ntext, count($cid)), 'message');
            }
        }

        $this->setRedirect('index.php?option=com_services&view=items');
    }

    /**
     * Method to unfeature a list of items
     *
     * @return  void
     */
    public function unfeature()
    {
        // Check for request forgeries
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Get items to unfeature from the request
        $cid = $this->input->get('cid', array(), 'array');

        if (!is_array($cid) || count($cid) < 1) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_NO_ITEMS_SELECTED'), 'warning');
        } else {
            // Get the model
            $model = $this->getModel();

            // Unfeature the items
            if (!$model->feature($cid, 0)) {
                Factory::getApplication()->enqueueMessage($model->getError(), 'error');
            } else {
                $ntext = $this->text_prefix . '_N_ITEMS_UNFEATURED';
                Factory::getApplication()->enqueueMessage(Text::plural($ntext, count($cid)), 'message');
            }
        }

        $this->setRedirect('index.php?option=com_services&view=items');
    }

    /**
     * Method to feature items (alias for feature method to match toolbar)
     *
     * @return  void
     */
    public function featured()
    {
        $this->feature();
    }

    /**
     * Method to unfeature items (alias for unfeature method to match toolbar)
     *
     * @return  void
     */
    public function unfeatured()
    {
        $this->unfeature();
    }

    /**
     * Method to delete items
     *
     * @return  void
     */
    public function delete()
    {
        // Check for request forgeries
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Get items to delete from the request
        $cid = $this->input->get('cid', array(), 'array');

        if (!is_array($cid) || count($cid) < 1) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_NO_ITEMS_SELECTED'), 'warning');
        } else {
            // Get the model
            $model = $this->getModel();

            // Delete the items
            if (!$model->delete($cid)) {
                Factory::getApplication()->enqueueMessage($model->getError(), 'error');
            } else {
                $ntext = $this->text_prefix . '_N_ITEMS_DELETED';
                Factory::getApplication()->enqueueMessage(Text::plural($ntext, count($cid)), 'message');
            }
        }

        $this->setRedirect('index.php?option=com_services&view=items');
    }
}
