<?php

/**
 * Yak Shaver Inventory — categories list model (admin)
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

class CategoriesModel extends ListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'published', 'a.published',
                'access', 'a.access',
                'access_level',
                'language', 'a.language',
                'lft', 'a.lft',
                'rgt', 'a.rgt',
                'level', 'a.level',
                'path', 'a.path',
                'parent_id', 'a.parent_id',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'created_user_id', 'a.created_user_id',
                'created_time', 'a.created_time',
            ];
        }

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'a.lft', $direction = 'asc')
    {
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.level');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select(
            $db->quoteName([
                'a.id',
                'a.title',
                'a.alias',
                'a.description',
                'a.published',
                'a.access',
                'a.checked_out',
                'a.checked_out_time',
                'a.lft',
                'a.rgt',
                'a.level',
                'a.path',
                'a.parent_id',
                'a.created_user_id',
                'a.created_time',
                'a.language',
            ])
        );

        $query->from($db->quoteName('#__ysi_categories', 'a'));

        // Exclude root category.
        $query->where($db->quoteName('a.id') . ' > 1');

        // Join access level.
        $query->select($db->quoteName('ag.title', 'access_level'))
            ->join(
                'LEFT',
                $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access')
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

        // Filter by access level.
        $access = (int) $this->getState('filter.access');

        if ($access > 0) {
            $query->where($db->quoteName('a.access') . ' = :access');
            $query->bind(':access', $access, ParameterType::INTEGER);
        }

        // Filter by level.
        $level = (int) $this->getState('filter.level');

        if ($level > 0) {
            $query->where($db->quoteName('a.level') . ' <= :level');
            $query->bind(':level', $level, ParameterType::INTEGER);
        }

        // Filter by language.
        $language = $this->getState('filter.language');

        if (!empty($language)) {
            $query->where($db->quoteName('a.language') . ' = :language');
            $query->bind(':language', $language);
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
                    '(' . $db->quoteName('a.title') . ' LIKE :title OR '
                    . $db->quoteName('a.alias') . ' LIKE :alias OR '
                    . $db->quoteName('a.path') . ' LIKE :path)'
                );
                $query->bind(':title', $search);
                $query->bind(':alias', $search);
                $query->bind(':path', $search);
            }
        }

        // Ordering.
        $orderCol  = $this->state->get('list.ordering', 'a.lft');
        $orderDirn = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
