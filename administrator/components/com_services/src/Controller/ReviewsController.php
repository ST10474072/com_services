<?php
namespace Jbaylet\Component\Services\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Reviews list controller class.
 */
class ReviewsController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_SERVICES_REVIEWS';

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     */
    public function getModel($name = 'Review', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * View all reviews for a specific business
     *
     * @return  void
     */
    public function viewbusiness()
    {
        $serviceId = $this->input->get('service_id', 0, 'int');
        
        if (empty($serviceId)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_ERROR_INVALID_BUSINESS'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_services&view=reviews'));
            return;
        }

        // Set the service ID in the application state for the view
        Factory::getApplication()->setUserState('com_services.reviews.service_id', $serviceId);
        
        // Redirect to business reviews detail view
        $this->setRedirect(Route::_('index.php?option=com_services&view=reviews&layout=business&service_id=' . (int) $serviceId));
    }

    /**
     * Approve selected reviews
     *
     * @return  void
     */
    public function approve()
    {
        // Check for request forgeries
        \Joomla\CMS\Session\Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Get reviews to approve from the request
        $cid = $this->input->get('cid', array(), 'array');

        if (!is_array($cid) || count($cid) < 1) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_NO_REVIEWS_SELECTED'), 'warning');
        } else {
            // Get the model
            $model = $this->getModel('Reviews');

            // Approve the reviews
            if (!$model->approve($cid)) {
                Factory::getApplication()->enqueueMessage($model->getError(), 'error');
            } else {
                $ntext = $this->text_prefix . '_N_ITEMS_APPROVED';
                Factory::getApplication()->enqueueMessage(Text::plural($ntext, count($cid)), 'message');
            }
        }

        $this->setRedirect(Route::_('index.php?option=com_services&view=reviews'));
    }

    /**
     * Reject selected reviews
     *
     * @return  void
     */
    public function reject()
    {
        // Check for request forgeries
        \Joomla\CMS\Session\Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Get reviews to reject from the request
        $cid = $this->input->get('cid', array(), 'array');

        if (!is_array($cid) || count($cid) < 1) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SERVICES_NO_REVIEWS_SELECTED'), 'warning');
        } else {
            // Get the model
            $model = $this->getModel('Reviews');

            // Reject the reviews
            if (!$model->reject($cid)) {
                Factory::getApplication()->enqueueMessage($model->getError(), 'error');
            } else {
                $ntext = $this->text_prefix . '_N_ITEMS_REJECTED';
                Factory::getApplication()->enqueueMessage(Text::plural($ntext, count($cid)), 'message');
            }
        }

        $this->setRedirect(Route::_('index.php?option=com_services&view=reviews'));
    }
}
