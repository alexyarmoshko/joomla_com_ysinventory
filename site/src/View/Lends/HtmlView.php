<?php

/**
 * Yak Shaver Inventory — loans list view (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\View\Lends;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    public $isModerator = false;
    public $assetFilterOptions = [];

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $user = $app->getIdentity();

        // Guests must log in.
        if ($user->guest) {
            $app->enqueueMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_LOGIN_REQUIRED'), 'warning');
            $app->redirect(Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Route::_('index.php?option=com_ysinventory&view=lends', false)), false));

            return;
        }

        $model = $this->getModel();

        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->isModerator = $model->isModerator($user);
        $this->assetFilterOptions = $model->getAssetFilterOptions();

        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if (!\is_array($this->items)) {
            $this->items = [];
        }

        $this->prepareBreadcrumbs();
        $this->prepareDocument();

        parent::display($tpl);
    }

    protected function prepareBreadcrumbs()
    {
        $app = Factory::getApplication();
        $pathway = $app->getPathway();

        $pathway->addItem(Text::_('COM_YSINVENTORY_LENDS'));
    }

    protected function prepareDocument()
    {
        $this->getDocument()->setTitle(Text::_('COM_YSINVENTORY_LENDS'));
    }
}
