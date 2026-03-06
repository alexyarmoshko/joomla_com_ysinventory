<?php

/**
 * Yak Shaver Inventory — tags grouped list model (site)
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

class TagsModel extends ListModel
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
                'tag_group_name',
            ];
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'tg.ordering', $direction = 'asc')
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
                'a.ysi_tag_group_id',
                'a.published',
                'a.ordering',
            ])
        );

        $query->from($db->quoteName('#__ysi_tags', 'a'));

        // Only published tags.
        $query->where($db->quoteName('a.published') . ' = 1');

        // Join tag group (only published groups).
        $query->select($db->quoteName([
            'tg.name',
            'tg.alias',
            'tg.ordering',
        ], [
            'tag_group_name',
            'tag_group_alias',
            'tag_group_ordering',
        ]))
            ->join(
                'INNER',
                $db->quoteName('#__ysi_tag_groups', 'tg')
                . ' ON ' . $db->quoteName('tg.id') . ' = ' . $db->quoteName('a.ysi_tag_group_id')
                . ' AND ' . $db->quoteName('tg.published') . ' = 1'
            );

        // Item count subquery (defensive - #__ysi_items and #__ysi_item_tag_map may not exist yet).
        try {
            $db->getTableColumns('#__ysi_item_tag_map', false);
            $groups = array_map('intval', $user->getAuthorisedViewLevels());
            $groupList = implode(',', $groups ?: [0]);

            $subQuery = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__ysi_item_tag_map', 'itm'))
                ->join(
                    'INNER',
                    $db->quoteName('#__ysi_items', 'i')
                    . ' ON ' . $db->quoteName('i.id') . ' = ' . $db->quoteName('itm.ysi_item_id')
                    . ' AND ' . $db->quoteName('i.published') . ' = 1'
                )
                ->where($db->quoteName('itm.ysi_tag_id') . ' = ' . $db->quoteName('a.id'));

            // Access filter on items.
            $itemColumns = $db->getTableColumns('#__ysi_items', false);

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
        } catch (\RuntimeException $e) {
            $query->select('0 AS ' . $db->quoteName('item_count'));
        }

        // Ordering: group ordering first, then tag ordering within group.
        $query->order($db->quoteName('tg.ordering') . ' ASC, ' . $db->quoteName('tg.name') . ' ASC, '
            . $db->quoteName('a.ordering') . ' ASC, ' . $db->quoteName('a.name') . ' ASC');

        return $query;
    }

    /**
     * Returns tags grouped by tag group for the template.
     *
     * @return array  Associative array keyed by tag group name, each containing
     *                'group' (object with name, alias) and 'tags' (array of tag objects).
     */
    public function getGroupedTags()
    {
        $items = $this->getItems();

        if (!\is_array($items)) {
            return [];
        }

        $grouped = [];

        foreach ($items as $item) {
            $groupKey = (int) $item->ysi_tag_group_id;

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'group' => (object) [
                        'id' => $groupKey,
                        'name' => $item->tag_group_name,
                        'alias' => $item->tag_group_alias,
                    ],
                    'tags' => [],
                ];
            }

            $grouped[$groupKey]['tags'][] = $item;
        }

        return $grouped;
    }
}
