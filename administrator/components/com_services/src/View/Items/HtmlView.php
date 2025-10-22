<?php
namespace Jbaylet\Component\Services\Administrator\View\Items;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of services items.
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
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

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

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('COM_SERVICES_ITEMS'), 'services');

        if ($user->authorise('core.create', 'com_services')) {
            $toolbar->addNew('item.add');
        }

        if ($user->authorise('core.edit.state', 'com_services')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish('items.publish')->listCheck(true);
            $childBar->unpublish('items.unpublish')->listCheck(true);

            $childBar->standardButton('featured')
                ->text('COM_SERVICES_TOOLBAR_FEATURE')
                ->task('items.featured')
                ->listCheck(true);

            $childBar->standardButton('unfeatured')
                ->text('COM_SERVICES_TOOLBAR_UNFEATURE')
                ->task('items.unfeatured')
                ->listCheck(true);

            if ($user->authorise('core.admin')) {
                $childBar->checkin('items.checkin');
            }

            if ($user->authorise('core.delete', 'com_services')) {
                $childBar->delete('items.delete')
                    ->text('JTOOLBAR_DELETE')
                    ->message('JGLOBAL_CONFIRM_DELETE')
                    ->listCheck(true);
            }
        }

        if ($user->authorise('core.admin', 'com_services') || $user->authorise('core.options', 'com_services')) {
            $toolbar->preferences('com_services');
        }

        $toolbar->help('JHELP_COMPONENTS_SERVICES_ITEMS');
    }
}