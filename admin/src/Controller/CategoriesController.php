<?php

/**
 * Yak Shaver Inventory — categories list controller (admin)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

class CategoriesController extends AdminController
{
    public function getModel($name = 'Category', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function rebuild()
    {
        $this->checkToken();

        if (!$this->app->getIdentity()->authorise('core.admin', 'com_ysinventory')) {
            $this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $this->setRedirect(Route::_($this->getRedirectToListRoute(), false));

            return;
        }

        $model = $this->getModel();

        if ($model->rebuild()) {
            $this->setMessage(Text::_('COM_YSINVENTORY_CATEGORIES_REBUILD_SUCCESS'));
        } else {
            $this->setMessage(Text::_('COM_YSINVENTORY_CATEGORIES_REBUILD_FAILURE'), 'error');
        }

        $this->setRedirect($this->getRedirectToListRoute());
    }

    protected function getRedirectToListRoute($append = '')
    {
        return 'index.php?option=com_ysinventory&view=categories' . $append;
    }
}
