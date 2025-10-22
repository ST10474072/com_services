<?php
defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The Services service provider for the site application.
 */
return new class implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     */
    public function register(Container $container)
    {
        $container->registerServiceProvider(new MVCFactory('\\Jbaylet\\Component\\Services'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Jbaylet\\Component\\Services'));
        $container->registerServiceProvider(new RouterFactory('\\Jbaylet\\Component\\Services'));
    }
};
