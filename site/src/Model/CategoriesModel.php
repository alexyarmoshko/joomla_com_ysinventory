<?php

/**
 * Yak Shaver Inventory — categories list model (site)
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

class CategoriesModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'lft', 'a.lft',
                'level', 'a.level',
                'published', 'a.published',
                'access', 'a.access',
            ];
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.lft', $direction = 'asc')
    {
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.parent_id');

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        $query->select(
            $db->quoteName([
                'a.id',
                'a.title',
                'a.alias',
                'a.description',
                'a.published',
                'a.access',
                'a.lft',
                'a.rgt',
                'a.level',
                'a.path',
                'a.parent_id',
                'a.metadesc',
                'a.metakey',
                'a.language',
            ])
        );

        $query->from($db->quoteName('#__ysi_categories', 'a'));

        // Exclude root.
        $query->where($db->quoteName('a.id') . ' > 1');

        // Only published.
        $query->where($db->quoteName('a.published') . ' = 1');

        // Access level filter.
        $groups = $user->getAuthorisedViewLevels();
        $query->whereIn($db->quoteName('a.access'), $groups);

        // Item count subquery (placeholder — #__ysi_items will exist from Phase 6).
        try {
            $itemColumns = $db->getTableColumns('#__ysi_items', false);

            if (isset($itemColumns['catid'])) {
                $subQuery = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__ysi_items', 'i'))
                    ->where($db->quoteName('i.catid') . ' = ' . $db->quoteName('a.id'))
                    ->where($db->quoteName('i.published') . ' = 1');

                // Filter item counts by access level when the column exists.
                if (isset($itemColumns['access'])) {
                    $subQuery->whereIn($db->quoteName('i.access'), $groups);
                }

                $query->select('(' . $subQuery . ') AS ' . $db->quoteName('item_count'));
            } else {
                $query->select('0 AS ' . $db->quoteName('item_count'));
            }
        } catch (\RuntimeException $e) {
            $query->select('0 AS ' . $db->quoteName('item_count'));
        }

        // Parent filter.
        $parentId = (int) $this->getState('filter.parent_id', 1);

        if ($parentId > 0) {
            $query->where($db->quoteName('a.parent_id') . ' = :parentId');
            $query->bind(':parentId', $parentId, ParameterType::INTEGER);
        }

        // Ordering.
        $orderCol  = $this->state->get('list.ordering', 'a.lft');
        $orderDirn = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
