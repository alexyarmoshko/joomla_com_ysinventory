<?php

/**
 * Yak Shaver Inventory — single lend edit view (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\View\Lend;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use YakShaver\Component\Ysinventory\Administrator\Helper\ModeratorHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $state;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $user = $app->getIdentity();

        // Must be logged in.
        if ($user->guest) {
            $app->enqueueMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_LOGIN_REQUIRED'), 'warning');
            $app->redirect(Route::_('index.php?option=com_users&view=login', false));

            return;
        }

        $model = $this->getModel();

        // Load item first so we can scope the moderation check by category.
        $this->item = $model->getItem();

        $catId = null;

        if ($this->item && !empty($this->item->ysi_item_id)) {
            $catId = $model->getItemCategoryId((int) $this->item->ysi_item_id);

            if ($catId <= 0) {
                $catId = null;
            }
        }

        // Moderator check scoped to the loan's item's category.
        if (!ModeratorHelper::isModerator($user, $catId)) {
            $app->enqueueMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_NOT_AUTHORISED_MODERATE'), 'error');
            $app->redirect(Route::_('index.php?option=com_ysinventory&view=lends', false));

            return;
        }

        $this->form = $model->getForm();
        $this->state = $model->getState();

        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->prepareDocument();

        parent::display($tpl);
    }

    protected function prepareDocument()
    {
        $isNew = empty($this->item->id);

        $this->getDocument()->setTitle(
            $isNew ? Text::_('COM_YSINVENTORY_LEND_NEW') : Text::_('COM_YSINVENTORY_LEND_EDIT')
        );
    }
}
