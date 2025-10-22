<?php
namespace Jbaylet\Component\Services\Administrator\View\Item;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $state;

    public function display($tpl = null)
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        if (count($errors = $this->get('Errors')))
        {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $isNew = (int) ($this->item->id ?? 0) === 0;

        ToolbarHelper::title($isNew ? Text::_('COM_SERVICES_ADD_SERVICE') : Text::_('COM_SERVICES_EDIT_SERVICE'), 'pencil-2');
        
        ToolbarHelper::apply('item.apply');
        ToolbarHelper::save('item.save');
        ToolbarHelper::save2new('item.save2new');
        
        if (!$isNew) {
            ToolbarHelper::save2copy('item.save2copy');
        }

        ToolbarHelper::cancel('item.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
