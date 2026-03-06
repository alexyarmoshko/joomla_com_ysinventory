<?php

/**
 * Yak Shaver Inventory — items list model (site)
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

class ItemsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'name',
                'ordering',
                'ysi_quantity',
            ];
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.ordering', $direction = 'asc')
    {
        $app = Factory::getApplication();

        $this->setState('filter.category', $app->getInput()->getInt('catid', 0));
        $this->setState('filter.inventory', $app->getInput()->getInt('inventory', 0));
        $this->setState('filter.brand', $app->getInput()->getInt('brand', 0));
        $this->setState('filter.tag', $app->getInput()->getInt('tag', 0));
        $this->setState('filter.location', $app->getInput()->getInt('location', 0));
        $this->setState('filter.search', $app->getInput()->getString('search', ''));

        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.category');
        $id .= ':' . $this->getState('filter.inventory');
        $id .= ':' . $this->getState('filter.brand');
        $id .= ':' . $this->getState('filter.tag');
        $id .= ':' . $this->getState('filter.location');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        $user = Factory::getApplication()->getIdentity();
        $viewLevels = $user->getAuthorisedViewLevels();

        $query->select(
            $db->quoteName([
                'a.id',
                'a.name',
                'a.alias',
                'a.description',
                'a.image',
                'a.ysi_model',
                'a.ysi_serial_number',
                'a.ysi_sku',
                'a.ysi_quantity',
                'a.ysi_inventory_id',
                'a.catid',
                'a.brand_id',
                'a.ysi_location_user_id',
                'a.published',
                'a.access',
                'a.ordering',
            ])
        );
        $query->from($db->quoteName('#__ysi_items', 'a'));

        // Only published items.
        $query->where($db->quoteName('a.published') . ' = 1');

        // Access filter.
        $query->whereIn($db->quoteName('a.access'), $viewLevels);

        // Item visibility inherits category visibility on the site.
        $query->join(
            'INNER',
            $db->quoteName('#__ysi_categories', 'cat')
            . ' ON ' . $db->quoteName('cat.id') . ' = ' . $db->quoteName('a.catid')
            . ' AND ' . $db->quoteName('cat.published') . ' = 1'
            . ' AND ' . $db->quoteName('cat.level') . ' > 0'
        );
        $query->whereIn($db->quoteName('cat.access'), $viewLevels);

        // Join inventory name.
        $query->select($db->quoteName('inv.name', 'inventory_name'))
            ->join(
                'LEFT',
                $db->quoteName('#__ysi_inventories', 'inv') . ' ON ' . $db->quoteName('inv.id') . ' = ' . $db->quoteName('a.ysi_inventory_id')
            );

        // Join category title.
        $query->select($db->quoteName('cat.title', 'category_title'));

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

        // Filter by category.
        $catId = (int) $this->getState('filter.category');

        if ($catId > 0) {
            $query->where($db->quoteName('a.catid') . ' = :catId');
            $query->bind(':catId', $catId, ParameterType::INTEGER);
        }

        // Filter by inventory.
        $inventoryId = (int) $this->getState('filter.inventory');

        if ($inventoryId > 0) {
            $query->where($db->quoteName('a.ysi_inventory_id') . ' = :inventoryId');
            $query->bind(':inventoryId', $inventoryId, ParameterType::INTEGER);
        }

        // Filter by brand.
        $brandId = (int) $this->getState('filter.brand');

        if ($brandId > 0) {
            $query->where($db->quoteName('a.brand_id') . ' = :brandId');
            $query->bind(':brandId', $brandId, ParameterType::INTEGER);
        }

        // Filter by tag (via join table).
        $tagId = (int) $this->getState('filter.tag');

        if ($tagId > 0) {
            $query->join(
                'INNER',
                $db->quoteName('#__ysi_item_tag_map', 'itm') . ' ON ' . $db->quoteName('itm.ysi_item_id') . ' = ' . $db->quoteName('a.id')
            );
            $query->where($db->quoteName('itm.ysi_tag_id') . ' = :tagId');
            $query->bind(':tagId', $tagId, ParameterType::INTEGER);
        }

        // Filter by location user.
        $locationId = (int) $this->getState('filter.location');

        if ($locationId > 0) {
            $query->where($db->quoteName('a.ysi_location_user_id') . ' = :locationId');
            $query->bind(':locationId', $locationId, ParameterType::INTEGER);
        }

        // Search by item name.
        $search = trim($this->getState('filter.search', ''));

        if ($search !== '') {
            $search = '%' . $search . '%';
            $query->where($db->quoteName('a.name') . ' LIKE :search');
            $query->bind(':search', $search);
        }

        // Ordering.
        $orderCol = $this->state->get('list.ordering', 'a.ordering');
        $orderDirn = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    /**
     * Load filter option lists for the frontend filter bar.
     */
    public function getFilterOptions()
    {
        $db = $this->getDatabase();
        $user = Factory::getApplication()->getIdentity();
        $viewLevels = $user->getAuthorisedViewLevels();
        $options = [];

        // Categories (only those with at least one accessible published item).
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('c.id') . ', ' . $db->quoteName('c.title'))
            ->from($db->quoteName('#__ysi_categories', 'c'))
            ->join('INNER', $db->quoteName('#__ysi_items', 'i') . ' ON ' . $db->quoteName('i.catid') . ' = ' . $db->quoteName('c.id'))
            ->where($db->quoteName('c.published') . ' = 1')
            ->where($db->quoteName('c.level') . ' > 0')
            ->whereIn($db->quoteName('c.access'), $viewLevels)
            ->where($db->quoteName('i.published') . ' = 1')
            ->whereIn($db->quoteName('i.access'), $viewLevels)
            ->order($db->quoteName('c.lft'));
        $db->setQuery($query);
        $options['categories'] = $db->loadObjectList() ?: [];

        // Inventories (only those with at least one accessible published item).
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('inv.id') . ', ' . $db->quoteName('inv.name'))
            ->from($db->quoteName('#__ysi_inventories', 'inv'))
            ->join('INNER', $db->quoteName('#__ysi_items', 'i') . ' ON ' . $db->quoteName('i.ysi_inventory_id') . ' = ' . $db->quoteName('inv.id'))
            ->join('INNER', $db->quoteName('#__ysi_categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('i.catid'))
            ->where($db->quoteName('inv.published') . ' = 1')
            ->where($db->quoteName('c.published') . ' = 1')
            ->where($db->quoteName('c.level') . ' > 0')
            ->whereIn($db->quoteName('c.access'), $viewLevels)
            ->where($db->quoteName('i.published') . ' = 1')
            ->whereIn($db->quoteName('i.access'), $viewLevels)
            ->order($db->quoteName('inv.name'));
        $db->setQuery($query);
        $options['inventories'] = $db->loadObjectList() ?: [];

        // Brands (only those with at least one accessible published item).
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('br.id') . ', ' . $db->quoteName('br.name'))
            ->from($db->quoteName('#__ysi_brands', 'br'))
            ->join('INNER', $db->quoteName('#__ysi_items', 'i') . ' ON ' . $db->quoteName('i.brand_id') . ' = ' . $db->quoteName('br.id'))
            ->join('INNER', $db->quoteName('#__ysi_categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('i.catid'))
            ->where($db->quoteName('br.published') . ' = 1')
            ->where($db->quoteName('c.published') . ' = 1')
            ->where($db->quoteName('c.level') . ' > 0')
            ->whereIn($db->quoteName('c.access'), $viewLevels)
            ->where($db->quoteName('i.published') . ' = 1')
            ->whereIn($db->quoteName('i.access'), $viewLevels)
            ->order($db->quoteName('br.name'));
        $db->setQuery($query);
        $options['brands'] = $db->loadObjectList() ?: [];

        // Tags (with group name prefix, only those assigned to at least one accessible published item).
        $query = $db->getQuery(true)
            ->select([
                'DISTINCT ' . $db->quoteName('t.id'),
                'CONCAT(' . $db->quoteName('tg.name') . ', ' . $db->quote(' — ') . ', ' . $db->quoteName('t.name') . ') AS ' . $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__ysi_tags', 't'))
            ->join('INNER', $db->quoteName('#__ysi_tag_groups', 'tg') . ' ON ' . $db->quoteName('tg.id') . ' = ' . $db->quoteName('t.ysi_tag_group_id'))
            ->join('INNER', $db->quoteName('#__ysi_item_tag_map', 'itm') . ' ON ' . $db->quoteName('itm.ysi_tag_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__ysi_items', 'i') . ' ON ' . $db->quoteName('i.id') . ' = ' . $db->quoteName('itm.ysi_item_id'))
            ->join('INNER', $db->quoteName('#__ysi_categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('i.catid'))
            ->where($db->quoteName('t.published') . ' = 1')
            ->where($db->quoteName('tg.published') . ' = 1')
            ->where($db->quoteName('c.published') . ' = 1')
            ->where($db->quoteName('c.level') . ' > 0')
            ->whereIn($db->quoteName('c.access'), $viewLevels)
            ->where($db->quoteName('i.published') . ' = 1')
            ->whereIn($db->quoteName('i.access'), $viewLevels)
            ->order([$db->quoteName('tg.ordering'), $db->quoteName('tg.name'), $db->quoteName('t.ordering'), $db->quoteName('t.name')]);
        $db->setQuery($query);
        $options['tags'] = $db->loadObjectList() ?: [];

        // Location users (only those assigned to at least one accessible published item).
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('u.id') . ', ' . $db->quoteName('u.name'))
            ->from($db->quoteName('#__users', 'u'))
            ->join('INNER', $db->quoteName('#__ysi_items', 'i') . ' ON ' . $db->quoteName('i.ysi_location_user_id') . ' = ' . $db->quoteName('u.id'))
            ->join('INNER', $db->quoteName('#__ysi_categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('i.catid'))
            ->where($db->quoteName('c.published') . ' = 1')
            ->where($db->quoteName('c.level') . ' > 0')
            ->whereIn($db->quoteName('c.access'), $viewLevels)
            ->where($db->quoteName('i.published') . ' = 1')
            ->whereIn($db->quoteName('i.access'), $viewLevels)
            ->order($db->quoteName('u.name'));
        $db->setQuery($query);
        $options['locations'] = $db->loadObjectList() ?: [];

        return $options;
    }
}
