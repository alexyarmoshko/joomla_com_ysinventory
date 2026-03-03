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
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

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

        $app = Factory::getApplication();
        $user = $app->getIdentity();

        // Must be logged in.
        if ($user->guest) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_LOGIN_REQUIRED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_ysinventory&view=items', false));

            return false;
        }

        // Extract input.
        $input = $app->getInput();
        $itemId = $input->getInt('ysi_item_id', 0);
        $from = $input->getString('ysi_from', '');
        $to = $input->getString('ysi_to', '');
        $note = $input->getString('ysi_note', '');

        $returnUrl = Route::_('index.php?option=com_ysinventory&view=item&id=' . $itemId, false);

        // Load item with access and catid.
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'ysi_quantity', 'access', 'catid']))
            ->from($db->quoteName('#__ysi_items'))
            ->where($db->quoteName('id') . ' = :itemId')
            ->where($db->quoteName('published') . ' = 1')
            ->bind(':itemId', $itemId, ParameterType::INTEGER);
        $db->setQuery($query);
        $item = $db->loadObject();

        if (!$item) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_ITEM_NOT_FOUND'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_ysinventory&view=items', false));

            return false;
        }

        // Access check (Finding 5): verify the user can view this item.
        if (!\in_array((int) $item->access, $user->getAuthorisedViewLevels(), true)) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_ACCESS_DENIED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_ysinventory&view=items', false));

            return false;
        }

        // Category-level auth with fallback to component params (Findings 1+2).
        $componentParams = ComponentHelper::getParams('com_ysinventory');
        $catParams = new Registry('{}');

        if ((int) $item->catid > 0) {
            $catQuery = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__ysi_categories'))
                ->where($db->quoteName('id') . ' = :catId')
                ->bind(':catId', $item->catid, ParameterType::INTEGER);
            $db->setQuery($catQuery);
            $catJson = $db->loadResult();

            if (!empty($catJson) && \is_string($catJson)) {
                $catParams = new Registry($catJson);
            }
        }

        $requestGroups = (array) $catParams->get('ysi_lend_request_groups', []);

        if (empty($requestGroups)) {
            $requestGroups = (array) $componentParams->get('ysi_lend_request_groups', []);
        }

        if (empty($requestGroups)) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_NOT_CONFIGURED'), 'error');
            $this->setRedirect($returnUrl);

            return false;
        }

        $userGroups = $user->getAuthorisedGroups();

        if (empty(array_intersect($userGroups, $requestGroups))) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_NOT_AUTHORISED'), 'error');
            $this->setRedirect($returnUrl);

            return false;
        }

        // Strict date validation (Finding 6).
        if (empty($from) || empty($to)) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_REQUIRE_DATES'), 'error');
            $this->setRedirect($returnUrl);

            return false;
        }

        $fromDate = \DateTimeImmutable::createFromFormat('Y-m-d', $from);
        $toDate = \DateTimeImmutable::createFromFormat('Y-m-d', $to);

        if (!$fromDate || $fromDate->format('Y-m-d') !== $from) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_INVALID_DATE_FORMAT'), 'error');
            $this->setRedirect($returnUrl);

            return false;
        }

        if (!$toDate || $toDate->format('Y-m-d') !== $to) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_INVALID_DATE_FORMAT'), 'error');
            $this->setRedirect($returnUrl);

            return false;
        }

        $today = Factory::getDate()->format('Y-m-d');

        if ($from < $today) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_FROM_PAST'), 'error');
            $this->setRedirect($returnUrl);

            return false;
        }

        if ($to <= $from) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_DATE_ORDER'), 'error');
            $this->setRedirect($returnUrl);

            return false;
        }

        // Stock check (Finding 3): simple quantity > 0 check.
        if ((int) $item->ysi_quantity <= 0) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_NO_STOCK'), 'error');
            $this->setRedirect($returnUrl);

            return false;
        }

        // Create lend record via Table.
        $table = $this->factory->createTable('Lend', 'Administrator');
        $table->setCurrentUser($user);

        $data = [
            'ysi_item_id' => $itemId,
            'ysi_user_id' => $user->id,
            'ysi_from' => $from,
            'ysi_to' => $to,
            'ysi_note' => $note,
            'ysi_status' => 1,
        ];

        if (!$table->bind($data) || !$table->check() || !$table->store()) {
            $this->setMessage($table->getError(), 'error');
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
