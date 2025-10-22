<?php
namespace Jbaylet\Component\Services\Administrator\View\Configuration;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for the Services configuration.
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The component parameters
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $item;

    /**
     * Display the view
     *
     * @param   string  $tpl  The template file to include
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Load language file explicitly
        $lang = Factory::getLanguage();
        $lang->load('com_services', JPATH_ADMINISTRATOR, null, false, true);
        $lang->load('com_services.sys', JPATH_ADMINISTRATOR, null, false, true);
        
        // Get data from the model
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

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
        Factory::getApplication()->input->set('hidemainmenu', true);

        ToolbarHelper::title(Text::_('COM_SERVICES_CONFIGURATION'), 'cogs');

        // Save button
        ToolbarHelper::save('configuration.save', 'JSAVE');

        // Cancel button
        ToolbarHelper::cancel('configuration.cancel', 'JCANCEL');

        // Restore Defaults button
        ToolbarHelper::custom('configuration.restoreDefaults', 'refresh', 'refresh', 'COM_SERVICES_RESTORE_DEFAULTS', false);
    }
}