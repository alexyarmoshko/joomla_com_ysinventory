<?php

/**
 * Yak Shaver Inventory — items list model (admin)
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

class ItemsModel extends ListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'a.id',
                'name',
                'a.name',
                'alias',
                'a.alias',
                'published',
                'a.published',
                'ordering',
                'a.ordering',
                'created',
                'a.created',
                'created_by',
                'a.created_by',
                'checked_out',
                'a.checked_out',
                'checked_out_time',
                'a.checked_out_time',
                'ysi_inventory_id',
                'a.ysi_inventory_id',
                'catid',
                'a.catid',
                'brand_id',
                'a.brand_id',
                'ysi_quantity',
                'a.ysi_quantity',
                'ysi_status',
                'a.ysi_status',
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
        $id .= ':' . $this->getState('filter.ysi_inventory_id');
        $id .= ':' . $this->getState('filter.catid');
        $id .= ':' . $this->getState('filter.brand_id');
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
                'a.name',
                'a.alias',
                'a.ysi_inventory_id',
                'a.catid',
                'a.brand_id',
                'a.ysi_location_user_id',
                'a.ysi_quantity',
                'a.ysi_status',
                'a.published',
                'a.checked_out',
                'a.checked_out_time',
                'a.ordering',
                'a.created',
                'a.created_by',
            ])
        );

        $query->from($db->quoteName('#__ysi_items', 'a'));

        // Join inventory name.
        $query->select($db->quoteName('inv.name', 'inventory_name'))
            ->join(
                'LEFT',
                $db->quoteName('#__ysi_inventories', 'inv') . ' ON ' . $db->quoteName('inv.id') . ' = ' . $db->quoteName('a.ysi_inventory_id')
            );

        // Join category title.
        $query->select($db->quoteName('cat.title', 'category_title'))
            ->join(
                'LEFT',
                $db->quoteName('#__ysi_categories', 'cat') . ' ON ' . $db->quoteName('cat.id') . ' = ' . $db->quoteName('a.catid')
            );

        // Join brand name.
        $query->select($db->quoteName('br.name', 'brand_name'))
            ->join(
                'LEFT',
                $db->quoteName('#__ysi_brands', 'br') . ' ON ' . $db->quoteName('br.id') . ' = ' . $db->quoteName('a.brand_id')
            );

        // Join location user name.
        $query->select($db->quoteName('lu.name', 'location_name'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'lu') . ' ON ' . $db->quoteName('lu.id') . ' = ' . $db->quoteName('a.ysi_location_user_id')
            );

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

        // Filter by inventory.
        $inventoryId = $this->getState('filter.ysi_inventory_id');

        if (is_numeric($inventoryId)) {
            $inventoryId = (int) $inventoryId;
            $query->where($db->quoteName('a.ysi_inventory_id') . ' = :inventoryId');
            $query->bind(':inventoryId', $inventoryId, ParameterType::INTEGER);
        }

        // Filter by category.
        $catId = $this->getState('filter.catid');

        if (is_numeric($catId)) {
            $catId = (int) $catId;
            $query->where($db->quoteName('a.catid') . ' = :catId');
            $query->bind(':catId', $catId, ParameterType::INTEGER);
        }

        // Filter by brand.
        $brandId = $this->getState('filter.brand_id');

        if (is_numeric($brandId)) {
            $brandId = (int) $brandId;
            $query->where($db->quoteName('a.brand_id') . ' = :brandId');
            $query->bind(':brandId', $brandId, ParameterType::INTEGER);
        }

        // Filter by status.
        $status = $this->getState('filter.ysi_status');

        if (is_numeric($status)) {
            $status = (int) $status;
            $query->where($db->quoteName('a.ysi_status') . ' = :status');
            $query->bind(':status', $status, ParameterType::INTEGER);
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
        $orderCol = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
