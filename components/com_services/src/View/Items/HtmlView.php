<?php
namespace Jbaylet\Component\Services\Site\View\Items;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;

class HtmlView extends BaseHtmlView
{
    /**
     * The list of items
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
     * The component parameters
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $params;

    /**
     * The available categories
     *
     * @var  array
     */
    public $categories;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     *
     * @throws  Exception
     */
    public function display($tpl = null)
    {
        try {
            // Get data from the model
            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');
            $this->state = $this->get('State');
            $this->params = $this->state->get('params');
            $this->categories = $this->get('Categories');

            // Check for errors
            if (count($errors = $this->get('Errors'))) {
                throw new GenericDataException(implode("\n", $errors), 500);
            }

            // Set the document title
            $document = Factory::getDocument();
            $document->setTitle(Text::_('COM_SERVICES_ITEMS_TITLE'));

            parent::display($tpl);
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
    }
}
