<?php

/**
 * Yak Shaver Inventory — admin display controller
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    /**
     * The default view for the admin side.
     *
     * @var string
     */
    protected $default_view = 'inventories';

    public function display($cachable = false, $urlparams = [])
    {
        $view = $this->input->get('view', $this->default_view);
        $layout = $this->input->get('layout', 'default');
        $id = $this->input->getInt('id');

        if ($view == 'inventory' && $layout == 'edit' && !$this->checkEditId('com_ysinventory.edit.inventory', $id)) {
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(\Joomla\CMS\Language\Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_ysinventory&view=inventories', false));

            return false;
        }

        if ($view == 'category' && $layout == 'edit' && !$this->checkEditId('com_ysinventory.edit.category', $id)) {
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(\Joomla\CMS\Language\Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_ysinventory&view=categories', false));

            return false;
        }

        if ($view == 'brand' && $layout == 'edit' && !$this->checkEditId('com_ysinventory.edit.brand', $id)) {
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(\Joomla\CMS\Language\Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_ysinventory&view=brands', false));

            return false;
        }

        if ($view == 'taggroup' && $layout == 'edit' && !$this->checkEditId('com_ysinventory.edit.taggroup', $id)) {
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(\Joomla\CMS\Language\Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_ysinventory&view=taggroups', false));

            return false;
        }

        if ($view == 'tag' && $layout == 'edit' && !$this->checkEditId('com_ysinventory.edit.tag', $id)) {
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(\Joomla\CMS\Language\Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_ysinventory&view=tags', false));

            return false;
        }

        if ($view == 'item' && $layout == 'edit' && !$this->checkEditId('com_ysinventory.edit.item', $id)) {
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(\Joomla\CMS\Language\Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_ysinventory&view=items', false));

            return false;
        }

        if ($view == 'lend' && $layout == 'edit' && !$this->checkEditId('com_ysinventory.edit.lend', $id)) {
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(\Joomla\CMS\Language\Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_ysinventory&view=lends', false));

            return false;
        }

        return parent::display();
    }
}
