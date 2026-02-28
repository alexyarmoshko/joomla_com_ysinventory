<?php

/**
 * Yak Shaver Inventory — component extension class
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;

class YsinventoryComponent extends MVCComponent implements BootableExtensionInterface
{
    use HTMLRegistryAwareTrait;

    public function boot(ContainerInterface $container)
    {
        // Placeholder — HTML helpers and additional bootstrap logic will be
        // registered here as the component grows.
    }
}
