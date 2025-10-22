<?php
namespace Jbaylet\Component\Services\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     */
    protected $default_view = 'items';

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \Joomla\CMS\Filter\InputFilter::clean()}.
     *
     * @return  \Joomla\CMS\MVC\Controller\BaseController|boolean  This object to support chaining or false on failure.
     */
    public function display($cachable = false, $urlparams = array())
    {
        $view = $this->input->get('view', $this->default_view);
        $id = $this->input->getInt('id');
        
        // Set the view
        $this->input->set('view', $view);
        
        // For single item views, make sure we have a valid ID
        if ($view === 'item' && !$id) {
            // Redirect to items list if no valid ID provided
            $this->setRedirect('index.php?option=com_services&view=items');
            return false;
        }
        
        try {
            return parent::display($cachable, $urlparams);
        } catch (Exception $e) {
            $app = Factory::getApplication();
            $app->enqueueMessage('Component Error: ' . $e->getMessage(), 'error');
            
            // Log the error for debugging
            Factory::getLog()->add('COM_SERVICES DisplayController Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), Factory::getLog()::ERROR);
            
            // Only redirect if this is not already the items view
            if ($view !== 'items') {
                $this->setRedirect('index.php?option=com_services&view=items');
            }
            return false;
        }
    }
}
