<?php
namespace Jbaylet\Component\Services\Administrator\View\Messages;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of messages.
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var  \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  \Joomla\CMS\Object\CMSObject
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * Input object
     *
     * @var  \Joomla\Input\Input
     */
    public $input;

    /**
     * Display the view
     *
     * @param   string  $tpl  Template name
     *
     * @return void
     *
     * @throws Exception
     */
    public function display($tpl = null)
    {
        // For chat layout, we don't need items list
        if ($tpl !== 'chat') {
            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');
            $this->filterForm = $this->get('FilterForm');
            $this->activeFilters = $this->get('ActiveFilters');
        }
        
        $this->state = $this->get('State');
        $this->input = Factory::getApplication()->input;

        // Check for errors.
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
        $user = Factory::getUser();

        ToolbarHelper::title(Text::_('COM_SERVICES_MESSAGES'), 'envelope');

        if ($user->authorise('core.admin', 'com_services') || $user->authorise('core.options', 'com_services')) {
            ToolbarHelper::preferences('com_services');
        }
    }
}