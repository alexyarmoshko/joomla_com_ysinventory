<?php

/**
 * Yak Shaver Inventory — inventories list model (admin)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

class InventoriesModel extends ListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'name', 'a.name',
                'alias', 'a.alias',
                'published', 'a.published',
                'ordering', 'a.ordering',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'ysi_contact_user_id', 'a.ysi_contact_user_id',
                'ysi_contact_contact_id', 'a.ysi_contact_contact_id',
                'contact_name',
            ];
        }

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'a.name', $direction = 'asc')
    {
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $inventoryColumns = $db->getTableColumns('#__ysi_inventories', false);

        $selectColumns = [
            'a.id',
            'a.name',
            'a.alias',
            'a.description',
            'a.published',
            'a.checked_out',
            'a.checked_out_time',
            'a.ordering',
            'a.created',
            'a.created_by',
        ];

        $hasContactUserId    = isset($inventoryColumns['ysi_contact_user_id']);
        $hasContactContactId = isset($inventoryColumns['ysi_contact_contact_id']);
        $hasLegacyContactId  = isset($inventoryColumns['ysi_contact_id']);

        if ($hasContactUserId) {
            $selectColumns[] = 'a.ysi_contact_user_id';
        }

        if ($hasContactContactId) {
            $selectColumns[] = 'a.ysi_contact_contact_id';
        }

        if ($hasLegacyContactId) {
            $selectColumns[] = 'a.ysi_contact_id';
        }

        $query->select($db->quoteName($selectColumns));
        $query->from($db->quoteName('#__ysi_inventories', 'a'));

        $effectiveUserIdExpression = '0';

        if ($hasContactUserId && $hasLegacyContactId) {
            $effectiveUserIdExpression = 'COALESCE(NULLIF(' . $db->quoteName('a.ysi_contact_user_id')
                . ', 0), NULLIF(' . $db->quoteName('a.ysi_contact_id') . ', 0))';
        } elseif ($hasContactUserId) {
            $effectiveUserIdExpression = $db->quoteName('a.ysi_contact_user_id');
        } elseif ($hasLegacyContactId) {
            $effectiveUserIdExpression = $db->quoteName('a.ysi_contact_id');
        }

        $hasUserJoin = $hasContactUserId || $hasLegacyContactId;

        if ($hasUserJoin) {
            $query->join(
                'LEFT',
                $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $effectiveUserIdExpression
            );
        }

        $hasContactDetailsTable = false;

        if ($hasContactContactId) {
            try {
                $hasContactDetailsTable = !empty($db->getTableColumns('#__contact_details', false));
            } catch (\RuntimeException $e) {
                $hasContactDetailsTable = false;
            }
        }

        if ($hasContactContactId && $hasContactDetailsTable) {
            $query->join(
                'LEFT',
                $db->quoteName('#__contact_details', 'cc') . ' ON ' . $db->quoteName('cc.id') . ' = ' . $db->quoteName('a.ysi_contact_contact_id')
            );

            $query->select(
                'CASE'
                . ' WHEN ' . $db->quoteName('a.ysi_contact_contact_id') . ' > 0 THEN ' . $db->quoteName('cc.name')
                . ' ELSE ' . ($hasUserJoin ? $db->quoteName('uc.name') : $db->quote(''))
                . ' END AS ' . $db->quoteName('contact_name')
            );

            $query->select(
                'CASE'
                . ' WHEN ' . $db->quoteName('a.ysi_contact_contact_id') . ' > 0 THEN ' . $db->quote('contact')
                . ($hasUserJoin ? ' WHEN ' . $effectiveUserIdExpression . ' > 0 THEN ' . $db->quote('user') : '')
                . ' ELSE ' . $db->quote('')
                . ' END AS ' . $db->quoteName('contact_type')
            );
        } elseif ($hasUserJoin) {
            $query->select($db->quoteName('uc.name', 'contact_name'));
            $query->select($db->quote('user') . ' AS ' . $db->quoteName('contact_type'));
        } else {
            $query->select($db->quote('') . ' AS ' . $db->quoteName('contact_name'));
            $query->select($db->quote('') . ' AS ' . $db->quoteName('contact_type'));
        }

        // Join checked-out user.
        $query->select($db->quoteName('uco.name', 'editor'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uco') . ' ON ' . $db->quoteName('uco.id') . ' = ' . $db->quoteName('a.checked_out')
            );

        // Filter by published state.
        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('a.published') . ' = :published');
            $query->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where('(' . $db->quoteName('a.published') . ' = 0 OR ' . $db->quoteName('a.published') . ' = 1)');
        }

        // Filter by search.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id');
                $query->bind(':id', $search, ParameterType::INTEGER);
            } else {
                $search = '%' . trim($search) . '%';
                $query->where(
                    '(' . $db->quoteName('a.name') . ' LIKE :name OR ' . $db->quoteName('a.alias') . ' LIKE :alias)'
                );
                $query->bind(':name', $search);
                $query->bind(':alias', $search);
            }
        }

        // Ordering.
        $orderCol  = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
