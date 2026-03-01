<?php

/**
 * Yak Shaver Inventory — single category controller (admin)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;

class CategoryController extends FormController
{
    protected function allowAdd($data = [])
    {
        return Factory::getApplication()->getIdentity()->authorise('core.create', 'com_ysinventory');
    }

    protected function allowEdit($data = [], $key = 'id')
    {
        $recordId = (int) ($data[$key] ?? 0);
        $user     = Factory::getApplication()->getIdentity();

        if ($user->authorise('core.edit', 'com_ysinventory.category.' . $recordId)) {
            return true;
        }

        if ($user->authorise('core.edit.own', 'com_ysinventory.category.' . $recordId)) {
            $record = $this->getModel()->getItem($recordId);

            if (!empty($record) && $record->created_user_id == $user->id) {
                return true;
            }
        }

        return false;
    }
}
