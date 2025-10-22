<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\Router\RouterBase;

/**
 * Simple working router for com_services
 */
class ServicesRouter extends RouterBase
{
    public function build(&$query)
    {
        $segments = [];
        
        if (isset($query['view'])) {
            unset($query['view']);
        }
        
        if (isset($query['id'])) {
            unset($query['id']);
        }
        
        if (isset($query['layout'])) {
            unset($query['layout']);
        }
        
        if (isset($query['task'])) {
            unset($query['task']);
        }
        
        return $segments;
    }

    public function parse(&$segments)
    {
        $vars = [];
        
        $app = Factory::getApplication();
        $menu = $app->getMenu();
        $active = $menu->getActive();
        
        if ($active && isset($active->query)) {
            foreach ($active->query as $key => $value) {
                if ($key != 'option' && $key != 'Itemid') {
                    $vars[$key] = $value;
                }
            }
        }
        
        if (!isset($vars['view'])) {
            $vars['view'] = 'items';
        }
        
        return $vars;
    }
}

function ServicesBuildRoute(&$query)
{
    $router = new ServicesRouter();
    return $router->build($query);
}

function ServicesParseRoute(&$segments)
{
    $router = new ServicesRouter();
    return $router->parse($segments);
}
