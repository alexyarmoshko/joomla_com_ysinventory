<?php

/**
 * Yak Shaver Inventory — brands list model (site)
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

class BrandsModel extends ListModel
{
    protected function shouldShowEmpty(): bool
    {
        $app = Factory::getApplication();
        return (int) $app->getParams()->get('show_empty', 1) === 1;
    }

    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'a.id',
                'name',
                'a.name',
                'ordering',
                'a.ordering',
                'published',
                'a.published',
            ];
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.name', $direction = 'asc')
    {
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . (int) $this->shouldShowEmpty();

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        $user = Factory::getApplication()->getIdentity();

        $query->select(
            $db->quoteName([
                'a.id',
                'a.name',
                'a.alias',
                'a.description',
                'a.image',
                'a.published',
            ])
        );

        $query->from($db->quoteName('#__ysi_brands', 'a'));

        // Only published.
        $query->where($db->quoteName('a.published') . ' = 1');

        // Item count subquery (defensive - #__ysi_items will exist from Phase 6).
        try {
            $itemColumns = $db->getTableColumns('#__ysi_items', false);
            $groups = array_map('intval', $user->getAuthorisedViewLevels());
            $groupList = implode(',', $groups ?: [0]);

            if (isset($itemColumns['brand_id'])) {
                $subQuery = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__ysi_items', 'i'))
                    ->where($db->quoteName('i.brand_id') . ' = ' . $db->quoteName('a.id'))
                    ->where($db->quoteName('i.published') . ' = 1');

                // Filter item counts by access level when the column exists.
                if (isset($itemColumns['access'])) {
                    $subQuery->where($db->quoteName('i.access') . ' IN (' . $groupList . ')');
                }

                if (isset($itemColumns['catid'])) {
                    $subQuery->join(
                        'INNER',
                        $db->quoteName('#__ysi_categories', 'c')
                        . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('i.catid')
                        . ' AND ' . $db->quoteName('c.published') . ' = 1'
                        . ' AND ' . $db->quoteName('c.level') . ' > 0'
                    );
                    $subQuery->where($db->quoteName('c.access') . ' IN (' . $groupList . ')');
                }

                $itemCountSql = '(' . $subQuery . ')';
                $query->select($itemCountSql . ' AS ' . $db->quoteName('item_count'));

                if (!$this->shouldShowEmpty()) {
                    $query->where($itemCountSql . ' > 0');
                }
            } else {
                $query->select('0 AS ' . $db->quoteName('item_count'));
            }
        } catch (\RuntimeException $e) {
            $query->select('0 AS ' . $db->quoteName('item_count'));
        }

        // Ordering.
        $orderCol = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
