<?php

/**
 * Yak Shaver Inventory — single category model (site)
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

class CategoryModel extends ListModel
{
    protected $category;

    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'title',
                'ordering',
            ];
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'i.ordering', $direction = 'asc')
    {
        $app = Factory::getApplication();

        $catId = $app->getInput()->getInt('id', 0);
        $this->setState('category.id', $catId);

        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('category.id');

        return parent::getStoreId($id);
    }

    public function getCategory()
    {
        if ($this->category !== null) {
            return $this->category;
        }

        $catId = (int) $this->getState('category.id');

        if ($catId <= 0) {
            $this->category = false;

            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName([
                'id', 'title', 'alias', 'description', 'published',
                'access', 'path', 'parent_id', 'level', 'metadesc', 'metakey', 'language',
            ]))
            ->from($db->quoteName('#__ysi_categories'))
            ->where($db->quoteName('id') . ' = :id')
            ->where($db->quoteName('published') . ' = 1')
            ->bind(':id', $catId, ParameterType::INTEGER);

        // Access filter.
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();
        $query->whereIn($db->quoteName('access'), $groups);

        $db->setQuery($query);
        $this->category = $db->loadObject();

        return $this->category;
    }

    public function getChildren()
    {
        $catId = (int) $this->getState('category.id');

        if ($catId <= 0) {
            return [];
        }

        $db    = $this->getDatabase();
        $user  = Factory::getApplication()->getIdentity();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['a.id', 'a.title', 'a.alias', 'a.description', 'a.path', 'a.level']))
            ->from($db->quoteName('#__ysi_categories', 'a'))
            ->where($db->quoteName('a.parent_id') . ' = :parentId')
            ->where($db->quoteName('a.published') . ' = 1')
            ->whereIn($db->quoteName('a.access'), $user->getAuthorisedViewLevels())
            ->order($db->quoteName('a.lft') . ' ASC')
            ->bind(':parentId', $catId, ParameterType::INTEGER);

        // Item count subquery (placeholder until #__ysi_items exists).
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
                    $subQuery->whereIn($db->quoteName('i.access'), $user->getAuthorisedViewLevels());
                }

                $query->select('(' . $subQuery . ') AS ' . $db->quoteName('item_count'));
            } else {
                $query->select('0 AS ' . $db->quoteName('item_count'));
            }
        } catch (\RuntimeException $e) {
            $query->select('0 AS ' . $db->quoteName('item_count'));
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    protected function getListQuery()
    {
        $catId = (int) $this->getState('category.id');
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Items in this category. The table may not exist yet (Phase 6 placeholder).
        try {
            $itemColumns = $db->getTableColumns('#__ysi_items', false);

            if (!isset($itemColumns['catid'])) {
                throw new \RuntimeException('Items table has no catid column');
            }

            $query->select($db->quoteName([
                'i.id', 'i.name', 'i.alias', 'i.catid',
                'i.published', 'i.ordering',
            ]));
            $query->from($db->quoteName('#__ysi_items', 'i'));
            $query->where($db->quoteName('i.catid') . ' = :catid');
            $query->where($db->quoteName('i.published') . ' = 1');
            $query->bind(':catid', $catId, ParameterType::INTEGER);

            // Access filter on items.
            $user = Factory::getApplication()->getIdentity();

            if (isset($itemColumns['access'])) {
                $query->whereIn($db->quoteName('i.access'), $user->getAuthorisedViewLevels());
            }

            $orderCol  = $this->state->get('list.ordering', 'i.ordering');
            $orderDirn = $this->state->get('list.direction', 'asc');
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        } catch (\RuntimeException $e) {
            // Items table does not exist yet — return empty resultset.
            $query->select('1 AS ' . $db->quoteName('id'));
            $query->select($db->quote('') . ' AS ' . $db->quoteName('name'));
            $query->from($db->quoteName('#__ysi_categories'));
            $query->where('0 = 1');
        }

        return $query;
    }
}
