<?php

/**
 * Yak Shaver Inventory — site lend controller
 *
 * Handles both loan-request submissions from item pages (request task)
 * and moderator CRUD via the frontend loans management view (save, cancel, add, edit).
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

class LendController extends FormController
{
    /**
     * The URL view list variable.
     *
     * @var string
     */
    protected $view_list = 'lends';

    /**
     * Check whether the user is allowed to add a new loan record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  bool
     */
    protected function allowAdd($data = [])
    {
        return $this->isModerator();
    }

    /**
     * Check whether the user is allowed to edit a loan record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  bool
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        return $this->isModerator();
    }

    /**
     * Submit a loan request from the item detail page.
     *
     * This is the original request flow kept intact.
     *
     * @return  bool
     */
    public function request()
    {
        // CSRF check.
        if (!Session::checkToken()) {
            $this->setMessage(Text::_('JINVALID_TOKEN'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_ysinventory&view=items', false));

            return false;
        }

        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        // Must be logged in.
        if ($user->guest) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_LOGIN_REQUIRED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_ysinventory&view=items', false));

            return false;
        }

        // Extract input.
        $input  = $app->getInput();
        $itemId = $input->getInt('ysi_item_id', 0);
        $from   = $input->getString('ysi_from', '');
        $to     = $input->getString('ysi_to', '');
        $note   = $input->getString('ysi_note', '');

        $returnUrl = Route::_('index.php?option=com_ysinventory&view=item&id=' . $itemId, false);

        /** @var \YakShaver\Component\Ysinventory\Site\Model\LendModel $model */
        $model = $this->getModel('Lend', 'Site');

        if (!$model->requestLoan($user, $itemId, $from, $to, $note)) {
            $this->setMessage($model->getError(), 'error');
            $this->setRedirect($returnUrl);

            return false;
        }

        $this->setMessage(Text::_('COM_YSINVENTORY_LEND_REQUEST_SUCCESS'));
        $this->setRedirect($returnUrl);

        return true;
    }

    /**
     * Check whether the current user belongs to a configured moderation group.
     *
     * @return  bool
     */
    private function isModerator()
    {
        $user = Factory::getApplication()->getIdentity();

        if ($user->guest) {
            return false;
        }

        if ($user->authorise('core.edit', 'com_ysinventory')) {
            return true;
        }

        $moderationGroups = (array) ComponentHelper::getParams('com_ysinventory')
            ->get('ysi_lend_moderation_groups', []);

        if (!empty($moderationGroups)) {
            $userGroups = $user->getAuthorisedGroups();

            if (!empty(array_intersect($userGroups, $moderationGroups))) {
                return true;
            }
        }

        return false;
    }
}
