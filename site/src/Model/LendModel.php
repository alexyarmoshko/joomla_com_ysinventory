<?php

/**
 * Yak Shaver Inventory — single lend model (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use YakShaver\Component\Ysinventory\Administrator\Helper\ModeratorHelper;

class LendModel extends AdminModel
{
    public $typeAlias = 'com_ysinventory.lend';

    protected $formName = 'lend';

    public function getForm($data = [], $loadData = true)
    {
        // Add admin form fallback path for deployments missing site/forms.
        Form::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/forms');

        $form = $this->loadForm(
            'com_ysinventory.' . $this->formName,
            $this->formName,
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }

        // Use the actual record for authorization when $data is empty (form bootstrap).
        $stateCheckData = $data;
        $item           = $this->getItem();

        if (empty($stateCheckData) || (int) $this->extractFieldValue($stateCheckData, 'ysi_item_id') <= 0) {
            $stateCheckData = $item;
        }

        $recordId = (int) $this->extractFieldValue($stateCheckData, 'id', $this->extractFieldValue($item, 'id', 0));
        $itemId   = (int) $this->extractFieldValue($stateCheckData, 'ysi_item_id', $this->getRequestedItemId());
        $loaneeId = (int) $this->extractFieldValue($stateCheckData, 'ysi_user_id', $this->extractFieldValue($item, 'ysi_user_id', 0));

        // For new records, restrict asset selector to categories the user moderates.
        if ($recordId <= 0) {
            $user = $this->getCurrentUser();

            if (!ModeratorHelper::isGlobalModerator($user)) {
                $modCatIds = ModeratorHelper::getModeratedCategoryIds($user);

                if (!empty($modCatIds)) {
                    $catList = implode(',', array_map('intval', $modCatIds));
                    $form->setFieldAttribute('ysi_item_id', 'query',
                        'SELECT id AS value, name AS text FROM #__ysi_items'
                        . ' WHERE published = 1 AND catid IN (' . $catList . ')'
                        . ' ORDER BY name'
                    );
                } else {
                    $form->setFieldAttribute('ysi_item_id', 'query',
                        'SELECT id AS value, name AS text FROM #__ysi_items WHERE 1 = 0'
                    );
                }
            }
        }

        // Restrict loanee options to request groups (category override, global fallback).
        $groupIds = $this->getRequestGroupIds($itemId);
        $form->setFieldAttribute('ysi_user_id', 'query', $this->buildLoaneeQuery($groupIds, $loaneeId));

        // On site edit, asset and loanee must remain immutable.
        if ($recordId > 0) {
            $form->setFieldAttribute('ysi_item_id', 'disabled', 'true');
            $form->setFieldAttribute('ysi_item_id', 'filter', 'unset');
            $form->setFieldAttribute('ysi_item_id', 'required', 'false');
            $form->setFieldAttribute('ysi_user_id', 'disabled', 'true');
            $form->setFieldAttribute('ysi_user_id', 'filter', 'unset');
            $form->setFieldAttribute('ysi_user_id', 'required', 'false');
        }

        if (!$this->canEditState((object) $stateCheckData)) {
            $form->setFieldAttribute('ysi_status', 'disabled', 'true');
            $form->setFieldAttribute('ysi_status', 'filter', 'unset');
        }

        return $form;
    }

    private function extractFieldValue($data, string $field, $default = null)
    {
        if (\is_array($data)) {
            return $data[$field] ?? $default;
        }

        if (\is_object($data) && isset($data->$field)) {
            return $data->$field;
        }

        return $default;
    }

    private function getRequestedItemId(): int
    {
        $jform = Factory::getApplication()->getInput()->get('jform', [], 'array');

        return (int) ($jform['ysi_item_id'] ?? 0);
    }

    private function getRequestGroupIds(int $itemId): array
    {
        $globalGroups = $this->sanitizeGroupIds(
            (array) ComponentHelper::getParams('com_ysinventory')->get('ysi_lend_request_groups', [])
        );

        if ($itemId > 0) {
            $categoryGroups = $this->getCategoryRequestGroupsByItemId($itemId);

            if (!empty($categoryGroups)) {
                return $categoryGroups;
            }

            return $globalGroups;
        }

        $allGroups = $globalGroups;
        $db        = $this->getDatabase();
        $query     = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__ysi_categories'));
        $db->setQuery($query);
        $rows = $db->loadColumn() ?: [];

        foreach ($rows as $paramsJson) {
            if (!\is_string($paramsJson) || trim($paramsJson) === '') {
                continue;
            }

            $params    = new Registry($paramsJson);
            $allGroups = array_merge($allGroups, (array) $params->get('ysi_lend_request_groups', []));
        }

        return $this->sanitizeGroupIds($allGroups);
    }

    private function getCategoryRequestGroupsByItemId(int $itemId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('catid'))
            ->from($db->quoteName('#__ysi_items'))
            ->where($db->quoteName('id') . ' = :itemId')
            ->bind(':itemId', $itemId, ParameterType::INTEGER);
        $db->setQuery($query);
        $catId = (int) $db->loadResult();

        if ($catId <= 0) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__ysi_categories'))
            ->where($db->quoteName('id') . ' = :catId')
            ->bind(':catId', $catId, ParameterType::INTEGER);
        $db->setQuery($query);
        $paramsJson = $db->loadResult();

        if (!\is_string($paramsJson) || trim($paramsJson) === '') {
            return [];
        }

        $params = new Registry($paramsJson);

        return $this->sanitizeGroupIds((array) $params->get('ysi_lend_request_groups', []));
    }

    private function sanitizeGroupIds(array $groupIds): array
    {
        $groupIds = array_map('intval', $groupIds);
        $groupIds = array_filter($groupIds, static fn (int $id): bool => $id > 0);
        $groupIds = array_values(array_unique($groupIds));

        return $groupIds;
    }

    private function buildLoaneeQuery(array $groupIds, int $includeUserId = 0): string
    {
        $includeUserId = (int) $includeUserId;

        if (empty($groupIds)) {
            if ($includeUserId > 0) {
                return 'SELECT u.id AS value, u.name AS text'
                    . ' FROM #__users AS u'
                    . ' WHERE u.block = 0 AND u.id = ' . $includeUserId
                    . ' ORDER BY u.name';
            }

            return 'SELECT u.id AS value, u.name AS text FROM #__users AS u WHERE 1 = 0';
        }

        $groupList = implode(',', $groupIds);
        $groupExpr = 'map.group_id IN (' . $groupList . ')';

        if ($includeUserId > 0) {
            $groupExpr = '(' . $groupExpr . ' OR u.id = ' . $includeUserId . ')';
        }

        return 'SELECT DISTINCT u.id AS value, u.name AS text'
            . ' FROM #__users AS u'
            . ' INNER JOIN #__user_usergroup_map AS map ON map.user_id = u.id'
            . ' WHERE u.block = 0 AND ' . $groupExpr
            . ' ORDER BY u.name';
    }

    protected function loadFormData()
    {
        $app = Factory::getApplication();
        $data = $app->getUserState('com_ysinventory.edit.lend.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_ysinventory.lend', $data);

        return $data;
    }

    protected function prepareTable($table)
    {
        $date = Factory::getDate()->toSql();

        if (empty($table->id)) {
            $table->created = $date;
        } else {
            $table->modified = $date;
            $table->modified_by = $this->getCurrentUser()->id;
        }
    }

    protected function canDelete($record)
    {
        if (empty($record->id)) {
            return false;
        }

        $user = $this->getCurrentUser();
        $itemId = (int) ($record->ysi_item_id ?? 0);
        $catId = ($itemId > 0) ? $this->getItemCategoryId($itemId) : 0;

        return ModeratorHelper::isModerator($user, $catId > 0 ? $catId : null);
    }

    protected function canEditState($record)
    {
        $user = $this->getCurrentUser();

        // Component ACL check.
        if ($user->authorise('core.edit.state', 'com_ysinventory')) {
            return true;
        }

        // Category-level moderation group check with component fallback.
        $itemId = (int) ($record->ysi_item_id ?? 0);

        if ($itemId <= 0 || $user->guest) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('catid'))
            ->from($db->quoteName('#__ysi_items'))
            ->where($db->quoteName('id') . ' = :itemId')
            ->bind(':itemId', $itemId, ParameterType::INTEGER);
        $db->setQuery($query);
        $catId = (int) $db->loadResult();

        $moderationGroups = [];

        if ($catId > 0) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__ysi_categories'))
                ->where($db->quoteName('id') . ' = :catId')
                ->bind(':catId', $catId, ParameterType::INTEGER);
            $db->setQuery($query);
            $catJson = $db->loadResult();

            if (!empty($catJson) && \is_string($catJson)) {
                $catParams = new Registry($catJson);
                $moderationGroups = (array) $catParams->get('ysi_lend_moderation_groups', []);
            }
        }

        if (empty($moderationGroups)) {
            $moderationGroups = (array) ComponentHelper::getParams('com_ysinventory')->get('ysi_lend_moderation_groups', []);
        }

        if (!empty($moderationGroups)) {
            $userGroups = $user->getAuthorisedGroups();

            if (!empty(array_intersect($userGroups, $moderationGroups))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process a loan request from the frontend item detail page.
     *
     * Validates the item, access, group authorization, dates, and stock,
     * then creates a new lend record with status "Requested".
     *
     * @param   \Joomla\CMS\User\User  $user    The requesting user.
     * @param   int                     $itemId  The asset ID.
     * @param   string                  $from    Start date (Y-m-d).
     * @param   string                  $to      End date (Y-m-d).
     * @param   string                  $note    Optional note.
     *
     * @return  bool  True on success, false on failure (error set via setError).
     */
    public function requestLoan($user, int $itemId, string $from, string $to, string $note): bool
    {
        // Load item with access and catid, requiring a visible category.
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['a.id', 'a.ysi_quantity', 'a.access', 'a.catid']))
            ->from($db->quoteName('#__ysi_items', 'a'))
            ->where($db->quoteName('a.id') . ' = :itemId')
            ->where($db->quoteName('a.published') . ' = 1')
            ->bind(':itemId', $itemId, ParameterType::INTEGER)
            ->join(
                'INNER',
                $db->quoteName('#__ysi_categories', 'cat')
                . ' ON ' . $db->quoteName('cat.id') . ' = ' . $db->quoteName('a.catid')
                . ' AND ' . $db->quoteName('cat.published') . ' = 1'
                . ' AND ' . $db->quoteName('cat.level') . ' > 0'
            )
            ->whereIn($db->quoteName('cat.access'), $user->getAuthorisedViewLevels());
        $db->setQuery($query);
        $item = $db->loadObject();

        if (!$item) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_ITEM_NOT_FOUND'));

            return false;
        }

        // Access check: verify the user can view this item.
        if (!\in_array((int) $item->access, $user->getAuthorisedViewLevels(), true)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_ACCESS_DENIED'));

            return false;
        }

        // Category-level auth with fallback to component params.
        $componentParams = ComponentHelper::getParams('com_ysinventory');

        /** @var ItemModel $itemModel */
        $itemModel = $this->getMVCFactory()->createModel('Item', 'Site', ['ignore_request' => true]);
        $catParams = $itemModel->getCategoryParams((int) $item->catid);

        $requestGroups = (array) $catParams->get('ysi_lend_request_groups', []);

        if (empty($requestGroups)) {
            $requestGroups = (array) $componentParams->get('ysi_lend_request_groups', []);
        }

        if (empty($requestGroups)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_NOT_CONFIGURED'));

            return false;
        }

        $userGroups = $user->getAuthorisedGroups();

        if (empty(array_intersect($userGroups, $requestGroups))) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_NOT_AUTHORISED'));

            return false;
        }

        // Date validation.
        if (empty($from) || empty($to)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_REQUIRE_DATES'));

            return false;
        }

        $fromDate = \DateTimeImmutable::createFromFormat('Y-m-d', $from);
        $toDate   = \DateTimeImmutable::createFromFormat('Y-m-d', $to);

        if (!$fromDate || $fromDate->format('Y-m-d') !== $from) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_INVALID_DATE_FORMAT'));

            return false;
        }

        if (!$toDate || $toDate->format('Y-m-d') !== $to) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_INVALID_DATE_FORMAT'));

            return false;
        }

        $today = Factory::getDate()->format('Y-m-d');

        if ($from < $today) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_FROM_PAST'));

            return false;
        }

        if ($to <= $from) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_DATE_ORDER'));

            return false;
        }

        // Stock check.
        if ((int) $item->ysi_quantity <= 0) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_NO_STOCK'));

            return false;
        }

        // Create lend record via Table.
        $table = $this->getTable('Lend', 'Administrator');
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
            $this->setError($table->getError());

            return false;
        }

        return true;
    }

    /**
     * Look up the category ID for a given item.
     *
     * @param   int  $itemId  Item ID.
     *
     * @return  int  Category ID (0 if not found).
     */
    public function getItemCategoryId(int $itemId): int
    {
        if ($itemId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('catid'))
            ->from($db->quoteName('#__ysi_items'))
            ->where($db->quoteName('id') . ' = :itemId')
            ->bind(':itemId', $itemId, ParameterType::INTEGER);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Check whether the given user belongs to a configured moderation group.
     *
     * @param   \Joomla\CMS\User\User  $user   The user to check.
     * @param   int|null               $catId  Optional category ID for scoped check.
     *
     * @return  bool
     */
    public function isModerator($user, ?int $catId = null)
    {
        return ModeratorHelper::isModerator($user, $catId);
    }
}
