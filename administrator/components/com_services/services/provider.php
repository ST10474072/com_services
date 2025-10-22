<?php
defined('_JEXEC') or die;

use Jbaylet\Component\Services\Administrator\Extension\ServicesComponent;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;

/**
 * The Services service provider for the administrator application.
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

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new ServicesComponent($container->get(ComponentDispatcherFactoryInterface::class));

                // Set MVC factory
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                
                // Set router factory
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};
