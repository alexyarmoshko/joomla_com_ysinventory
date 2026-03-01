<?php

/**
 * Yak Shaver Inventory — single tag model (site)
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

class TagModel extends ListModel
{
    protected $tag;

    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'name',
                'ordering',
            ];
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'i.ordering', $direction = 'asc')
    {
        $app = Factory::getApplication();

        $tagId = $app->getInput()->getInt('id', 0);
        $this->setState('tag.id', $tagId);

        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('tag.id');

        return parent::getStoreId($id);
    }

    public function getTag()
    {
        if ($this->tag !== null) {
            return $this->tag;
        }

        $tagId = (int) $this->getState('tag.id');

        if ($tagId <= 0) {
            $this->tag = false;

            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName([
                'a.id',
                'a.name',
                'a.alias',
                'a.description',
                'a.ysi_tag_group_id',
                'a.published',
            ]))
            ->from($db->quoteName('#__ysi_tags', 'a'))
            ->where($db->quoteName('a.id') . ' = :id')
            ->where($db->quoteName('a.published') . ' = 1')
            ->bind(':id', $tagId, ParameterType::INTEGER);

        // Join tag group — require a published group for consistency with the grouped list view.
        $query->select($db->quoteName('tg.name', 'tag_group_name'))
            ->join(
                'INNER',
                $db->quoteName('#__ysi_tag_groups', 'tg')
                . ' ON ' . $db->quoteName('tg.id') . ' = ' . $db->quoteName('a.ysi_tag_group_id')
                . ' AND ' . $db->quoteName('tg.published') . ' = 1'
            );

        $db->setQuery($query);
        $this->tag = $db->loadObject();

        return $this->tag;
    }

    protected function getListQuery()
    {
        $tagId = (int) $this->getState('tag.id');
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        // Items belonging to this tag via join table.
        try {
            $db->getTableColumns('#__ysi_item_tag_map', false);
            $itemColumns = $db->getTableColumns('#__ysi_items', false);

            $query->select($db->quoteName([
                'i.id',
                'i.name',
                'i.alias',
                'i.published',
                'i.ordering',
            ]));
            $query->from($db->quoteName('#__ysi_item_tag_map', 'itm'));
            $query->join(
                'INNER',
                $db->quoteName('#__ysi_items', 'i')
                . ' ON ' . $db->quoteName('i.id') . ' = ' . $db->quoteName('itm.item_id')
            );
            $query->where($db->quoteName('itm.tag_id') . ' = :tagId');
            $query->where($db->quoteName('i.published') . ' = 1');
            $query->bind(':tagId', $tagId, ParameterType::INTEGER);

            // Access filter on items.
            $user = Factory::getApplication()->getIdentity();

            if (isset($itemColumns['access'])) {
                $query->whereIn($db->quoteName('i.access'), $user->getAuthorisedViewLevels());
            }

            $orderCol = $this->state->get('list.ordering', 'i.ordering');
            $orderDirn = $this->state->get('list.direction', 'asc');
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        } catch (\RuntimeException $e) {
            // Item/map tables do not exist yet — return empty resultset.
            $query->select('1 AS ' . $db->quoteName('id'));
            $query->select($db->quote('') . ' AS ' . $db->quoteName('name'));
            $query->from($db->quoteName('#__ysi_tags'));
            $query->where('0 = 1');
        }

        return $query;
    }
}
