<?php

/**
 * Yak Shaver Inventory — loans list model (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use YakShaver\Component\Ysinventory\Administrator\Helper\ModeratorHelper;

class LendsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'a.id',
                'ysi_status',
                'a.ysi_status',
                'ysi_item_id',
                'a.ysi_item_id',
                'ysi_user_id',
                'a.ysi_user_id',
                'ysi_from',
                'a.ysi_from',
                'ysi_to',
                'a.ysi_to',
                'created',
                'a.created',
            ];
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.created', $direction = 'desc')
    {
        $app = Factory::getApplication();

        $this->setState('filter.search', $app->getInput()->getString('search', ''));
        $this->setState('filter.ysi_status', $app->getInput()->getString('ysi_status', ''));
        $this->setState('filter.ysi_item_id', $app->getInput()->getString('ysi_item_id', ''));

        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.ysi_status');
        $id .= ':' . $this->getState('filter.ysi_item_id');

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select(
            $db->quoteName([
                'a.id',
                'a.ysi_item_id',
                'a.ysi_user_id',
                'a.ysi_from',
                'a.ysi_to',
                'a.ysi_note',
                'a.ysi_status',
                'a.created',
                'a.created_by',
            ])
        );

        $query->from($db->quoteName('#__ysi_lends', 'a'));

        // Join item name.
        $query->select($db->quoteName('it.name', 'item_name'))
            ->join(
                'LEFT',
                $db->quoteName('#__ysi_items', 'it') . ' ON ' . $db->quoteName('it.id') . ' = ' . $db->quoteName('a.ysi_item_id')
            );

        // Join user name.
        $query->select($db->quoteName('u.name', 'user_name'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.ysi_user_id')
            );

        // Visibility gating: global moderators see all, category moderators see
        // loans in their moderated categories plus own, others see only own.
        $user = Factory::getApplication()->getIdentity();

        if (!$user->guest && !ModeratorHelper::isGlobalModerator($user)) {
            $userId = (int) $user->id;
            $modCatIds = ModeratorHelper::getModeratedCategoryIds($user);

            if (!empty($modCatIds)) {
                $catList = implode(',', array_map('intval', $modCatIds));
                $query->where(
                    '(' . $db->quoteName('a.ysi_user_id') . ' = :currentUserId'
                    . ' OR ' . $db->quoteName('it.catid') . ' IN (' . $catList . '))'
                );
            } else {
                $query->where($db->quoteName('a.ysi_user_id') . ' = :currentUserId');
            }

            $query->bind(':currentUserId', $userId, ParameterType::INTEGER);
        }

        // Filter by item (asset).
        $itemId = $this->getState('filter.ysi_item_id');

        if (is_numeric($itemId)) {
            $itemId = (int) $itemId;
            $query->where($db->quoteName('a.ysi_item_id') . ' = :filterItemId');
            $query->bind(':filterItemId', $itemId, ParameterType::INTEGER);
        }

        // Filter by status.
        $status = $this->getState('filter.ysi_status');

        if (is_numeric($status)) {
            $status = (int) $status;
            $query->where($db->quoteName('a.ysi_status') . ' = :status');
            $query->bind(':status', $status, ParameterType::INTEGER);
        }

        // Filter by search (borrower name).
        $search = trim($this->getState('filter.search', ''));

        if ($search !== '') {
            $search = '%' . $search . '%';
            $query->where($db->quoteName('u.name') . ' LIKE :userName');
            $query->bind(':userName', $search);
        }

        // Ordering.
        $orderCol = $this->state->get('list.ordering', 'a.created');
        $orderDirn = $this->state->get('list.direction', 'desc');

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    /**
     * Get distinct assets that have loan records, for the filter dropdown.
     *
     * Applies the same moderator/non-moderator visibility rule as the main
     * list query so non-moderators only see assets from their own loans.
     *
     * @return  array  Array of objects with ->value and ->text properties.
     */
    public function getAssetFilterOptions(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('it.id', 'value'))
            ->select($db->quoteName('it.name', 'text'))
            ->from($db->quoteName('#__ysi_lends', 'l'))
            ->join(
                'INNER',
                $db->quoteName('#__ysi_items', 'it') . ' ON ' . $db->quoteName('it.id') . ' = ' . $db->quoteName('l.ysi_item_id')
            );

        // Apply the same three-tier visibility as the main list query.
        $user = Factory::getApplication()->getIdentity();

        if (!$user->guest && !ModeratorHelper::isGlobalModerator($user)) {
            $userId = (int) $user->id;
            $modCatIds = ModeratorHelper::getModeratedCategoryIds($user);

            if (!empty($modCatIds)) {
                $catList = implode(',', array_map('intval', $modCatIds));
                $query->where(
                    '(' . $db->quoteName('l.ysi_user_id') . ' = :currentUserId'
                    . ' OR ' . $db->quoteName('it.catid') . ' IN (' . $catList . '))'
                );
            } else {
                $query->where($db->quoteName('l.ysi_user_id') . ' = :currentUserId');
            }

            $query->bind(':currentUserId', $userId, ParameterType::INTEGER);
        }

        $query->order($db->quoteName('it.name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Check whether the given user belongs to a configured moderation group.
     *
     * @param   \Joomla\CMS\User\User  $user  The user to check.
     *
     * @return  bool
     */
    public function isModerator($user)
    {
        return ModeratorHelper::isModerator($user);
    }
}
