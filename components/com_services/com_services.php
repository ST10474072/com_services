<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;

// Load component CSS and JS
HTMLHelper::_('stylesheet', 'com_services/style.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_services/script.js', array('version' => 'auto', 'relative' => true));

// Get the application
$app = Factory::getApplication();
$input = $app->getInput();

// Get the controller
$controller = BaseController::getInstance('Services', array('base_path' => JPATH_COMPONENT_SITE));

// Execute the task
$controller->execute($input->get('task'));
$controller->redirect();
