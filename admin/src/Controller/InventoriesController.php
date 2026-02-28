<?php

/**
 * Yak Shaver Inventory — inventories list controller (admin)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class InventoriesController extends AdminController
{
    public function getModel($name = 'Inventory', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
