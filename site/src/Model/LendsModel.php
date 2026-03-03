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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

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

        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.ysi_status');

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

        // Visibility gating: moderators see all, regular users see only own loans.
        $user = Factory::getApplication()->getIdentity();

        if (!$user->guest && !$this->isModerator($user)) {
            $userId = (int) $user->id;
            $query->where($db->quoteName('a.ysi_user_id') . ' = :currentUserId');
            $query->bind(':currentUserId', $userId, ParameterType::INTEGER);
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
     * Check whether the given user belongs to a configured moderation group.
     *
     * Checks category-level params first (if a category context is available),
     * then falls back to component-level params.
     *
     * @param   \Joomla\CMS\User\User  $user  The user to check.
     *
     * @return  bool
     */
    public function isModerator($user)
    {
        if ($user->guest) {
            return false;
        }

        // Component ACL shortcut.
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
