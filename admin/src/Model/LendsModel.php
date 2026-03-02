<?php

/**
 * Yak Shaver Inventory — lends list model (admin)
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

class LendsModel extends ListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
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

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'a.created', $direction = 'desc')
    {
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

        // Filter by status.
        $status = $this->getState('filter.ysi_status');

        if (is_numeric($status)) {
            $status = (int) $status;
            $query->where($db->quoteName('a.ysi_status') . ' = :status');
            $query->bind(':status', $status, ParameterType::INTEGER);
        }

        // Filter by item.
        $itemId = $this->getState('filter.ysi_item_id');

        if (is_numeric($itemId)) {
            $itemId = (int) $itemId;
            $query->where($db->quoteName('a.ysi_item_id') . ' = :itemId');
            $query->bind(':itemId', $itemId, ParameterType::INTEGER);
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
                $query->where($db->quoteName('u.name') . ' LIKE :userName');
                $query->bind(':userName', $search);
            }
        }

        // Ordering.
        $orderCol = $this->state->get('list.ordering', 'a.created');
        $orderDirn = $this->state->get('list.direction', 'desc');

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
