<?php

/**
 * Yak Shaver Inventory — service provider
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use YakShaver\Component\Ysinventory\Administrator\Extension\YsinventoryComponent;

return new class () implements ServiceProviderInterface {
    public function register(Container $container)
    {
        $container->registerServiceProvider(new MVCFactory('\\YakShaver\\Component\\Ysinventory'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\YakShaver\\Component\\Ysinventory'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new YsinventoryComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                return $component;
            }
        );
    }
};
