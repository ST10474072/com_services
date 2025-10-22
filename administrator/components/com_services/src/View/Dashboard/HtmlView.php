<?php
namespace Jbaylet\Component\Services\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for the Services dashboard.
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The statistics data
     *
     * @var  object
     */
    protected $stats;

    /**
     * Recent services
     *
     * @var  array
     */
    protected $recentServices;

    /**
     * Recent reviews
     *
     * @var  array
     */
    protected $recentReviews;

    /**
     * Category statistics
     *
     * @var  array
     */
    protected $categoryStats;

    /**
     * Pending reviews
     *
     * @var  array
     */
    protected $pendingReviews;

    /**
     * Display the view
     *
     * @param   string  $tpl  The template file to include
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get data from the model
        $this->stats = $this->get('Stats');
        $this->recentServices = $this->get('RecentServices');
        $this->recentReviews = $this->get('RecentReviews');
        $this->categoryStats = $this->get('CategoryStats');
        $this->pendingReviews = $this->get('PendingReviews');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_SERVICES_DASHBOARD'), 'fas fa-tachometer-alt');
        
        // Add preferences button
        if (Factory::getUser()->authorise('core.admin', 'com_services')) {
            ToolbarHelper::preferences('com_services');
        }
    }
}