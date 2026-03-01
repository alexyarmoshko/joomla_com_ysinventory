<?php

/**
 * Yak Shaver Inventory — tags list model (admin)
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

class TagsModel extends ListModel
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
                'ysi_tag_group_id',
                'a.ysi_tag_group_id',
                'tag_group_name',
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
        $id .= ':' . $this->getState('filter.tag_group_id');

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
                'a.description',
                'a.ysi_tag_group_id',
                'a.published',
                'a.checked_out',
                'a.checked_out_time',
                'a.ordering',
                'a.created',
                'a.created_by',
            ])
        );

        $query->from($db->quoteName('#__ysi_tags', 'a'));

        // Join tag group name.
        $query->select($db->quoteName('tg.name', 'tag_group_name'))
            ->join(
                'LEFT',
                $db->quoteName('#__ysi_tag_groups', 'tg') . ' ON ' . $db->quoteName('tg.id') . ' = ' . $db->quoteName('a.ysi_tag_group_id')
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

        // Filter by tag group.
        $tagGroupId = (int) $this->getState('filter.tag_group_id');

        if ($tagGroupId > 0) {
            $query->where($db->quoteName('a.ysi_tag_group_id') . ' = :tagGroupId');
            $query->bind(':tagGroupId', $tagGroupId, ParameterType::INTEGER);
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
