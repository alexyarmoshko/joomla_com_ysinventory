<?php

/**
 * Yak Shaver Inventory — site lend request controller
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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\ParameterType;

class LendController extends BaseController
{
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

        // Check user is in a request group.
        $params = ComponentHelper::getParams('com_ysinventory');
        $requestGroups = $params->get('ysi_lend_request_groups', []);

        if (empty($requestGroups)) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_NOT_CONFIGURED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_ysinventory&view=items', false));

            return false;
        }

        $userGroups = $user->getAuthorisedGroups();
        $requestGroups = (array) $requestGroups;

        if (empty(array_intersect($userGroups, $requestGroups))) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_NOT_AUTHORISED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_ysinventory&view=items', false));

            return false;
        }

        $input = $app->getInput();
        $itemId = $input->getInt('ysi_item_id', 0);
        $from = $input->getString('ysi_from', '');
        $to = $input->getString('ysi_to', '');
        $note = $input->getString('ysi_note', '');

        $returnUrl = Route::_('index.php?option=com_ysinventory&view=item&id=' . $itemId, false);

        // Validate item exists and is published.
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('ysi_quantity')])
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

        // Validate dates.
        if (empty($from) || empty($to)) {
            $this->setMessage(Text::_('COM_YSINVENTORY_ERROR_LEND_REQUIRE_DATES'), 'error');
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

        // Stock check.
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__ysi_lends'))
            ->where($db->quoteName('ysi_item_id') . ' = :stockItemId')
            ->where($db->quoteName('ysi_status') . ' = 2')
            ->bind(':stockItemId', $itemId, ParameterType::INTEGER);
        $db->setQuery($query);
        $borrowedCount = (int) $db->loadResult();

        if ($borrowedCount >= (int) $item->ysi_quantity) {
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
            'ysi_from'    => $from,
            'ysi_to'      => $to,
            'ysi_note'    => $note,
            'ysi_status'  => 1,
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
}
