<?php
namespace Jbaylet\Component\Services\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

class ItemController extends FormController
{
    protected $view_list = 'items';

    public function getModel($name = 'Item', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
