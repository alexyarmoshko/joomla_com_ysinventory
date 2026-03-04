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

    /**
     * Edit-view to list-view mapping for edit-lock checks.
     *
     * @var array<string, string>
     */
    private const EDIT_VIEW_MAP = [
        'inventory' => 'inventories',
        'category'  => 'categories',
        'brand'     => 'brands',
        'taggroup'  => 'taggroups',
        'tag'       => 'tags',
        'item'      => 'items',
        'lend'      => 'lends',
    ];

    public function display($cachable = false, $urlparams = [])
    {
        $view   = $this->input->get('view', $this->default_view);
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');

        if ($layout === 'edit' && isset(self::EDIT_VIEW_MAP[$view])
            && !$this->checkEditId('com_ysinventory.edit.' . $view, $id)
        ) {
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(\Joomla\CMS\Language\Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(
                \Joomla\CMS\Router\Route::_('index.php?option=com_ysinventory&view=' . self::EDIT_VIEW_MAP[$view], false)
            );

            return false;
        }

        return parent::display();
    }
}
