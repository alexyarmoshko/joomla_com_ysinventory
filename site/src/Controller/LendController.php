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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\ParameterType;
use YakShaver\Component\Ysinventory\Administrator\Helper\ModeratorHelper;

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
     * During save (when $data contains ysi_item_id), verifies the user
     * moderates the selected item's category. During form open ($data empty),
     * allows access if the user is a moderator for any scope.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  bool
     */
    protected function allowAdd($data = [])
    {
        $user = Factory::getApplication()->getIdentity();
        $itemId = (int) ($data['ysi_item_id'] ?? 0);

        if ($itemId > 0) {
            $catId = $this->getLoanCategoryId(['ysi_item_id' => $itemId], 'id');

            return ModeratorHelper::isModerator($user, $catId);
        }

        return ModeratorHelper::isModerator($user);
    }

    /**
     * Check whether the user is allowed to edit a loan record.
     *
     * Scopes the check to the loan's item's category so category-level
     * moderators can only edit loans for items in their moderated categories.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  bool
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $user = Factory::getApplication()->getIdentity();
        $catId = $this->getLoanCategoryId($data, $key);

        return ModeratorHelper::isModerator($user, $catId);
    }

    /**
     * Resolve the category ID for the item linked to a loan record.
     *
     * Checks form data for ysi_item_id first, then falls back to loading
     * the loan record by primary key.
     *
     * @param   array   $data  Form/request data.
     * @param   string  $key   Primary key field name.
     *
     * @return  int|null  Category ID, or null if undetermined.
     */
    private function getLoanCategoryId(array $data, string $key): ?int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $itemId = (int) ($data['ysi_item_id'] ?? 0);

        if ($itemId <= 0) {
            $recordId = (int) ($data[$key] ?? 0);

            if ($recordId <= 0) {
                return null;
            }

            $query = $db->getQuery(true)
                ->select($db->quoteName('ysi_item_id'))
                ->from($db->quoteName('#__ysi_lends'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $recordId, ParameterType::INTEGER);
            $db->setQuery($query);
            $itemId = (int) $db->loadResult();
        }

        if ($itemId <= 0) {
            return null;
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('catid'))
            ->from($db->quoteName('#__ysi_items'))
            ->where($db->quoteName('id') . ' = :itemId')
            ->bind(':itemId', $itemId, ParameterType::INTEGER);
        $db->setQuery($query);
        $catId = (int) $db->loadResult();

        return $catId > 0 ? $catId : null;
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

}
