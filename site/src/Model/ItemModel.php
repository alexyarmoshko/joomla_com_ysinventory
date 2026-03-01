<?php

/**
 * Yak Shaver Inventory — single item model (site)
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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

class ItemModel extends BaseDatabaseModel
{
    protected $item;

    public function getItem()
    {
        if ($this->item !== null) {
            return $this->item;
        }

        $app = Factory::getApplication();
        $itemId = $app->getInput()->getInt('id', 0);

        if ($itemId <= 0) {
            $this->item = false;

            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName([
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
        ]));
        $query->from($db->quoteName('#__ysi_items', 'a'));
        $query->where($db->quoteName('a.id') . ' = :itemId');
        $query->where($db->quoteName('a.published') . ' = 1');
        $query->bind(':itemId', $itemId, ParameterType::INTEGER);

        // Access filter.
        $user = Factory::getApplication()->getIdentity();
        $query->whereIn($db->quoteName('a.access'), $user->getAuthorisedViewLevels());

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

        $db->setQuery($query);
        $this->item = $db->loadObject();

        // Load associated tags with group names.
        if ($this->item) {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('t.id'),
                    $db->quoteName('t.name'),
                    $db->quoteName('t.alias'),
                    $db->quoteName('tg.name', 'group_name'),
                ])
                ->from($db->quoteName('#__ysi_item_tag_map', 'itm'))
                ->join('INNER', $db->quoteName('#__ysi_tags', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('itm.ysi_tag_id'))
                ->join('INNER', $db->quoteName('#__ysi_tag_groups', 'tg') . ' ON ' . $db->quoteName('tg.id') . ' = ' . $db->quoteName('t.ysi_tag_group_id'))
                ->where($db->quoteName('itm.ysi_item_id') . ' = :itemId2')
                ->where($db->quoteName('t.published') . ' = 1')
                ->where($db->quoteName('tg.published') . ' = 1')
                ->bind(':itemId2', $this->item->id, ParameterType::INTEGER)
                ->order([$db->quoteName('tg.ordering'), $db->quoteName('t.ordering')]);
            $db->setQuery($query);
            $this->item->tags = $db->loadObjectList() ?: [];
        }

        return $this->item;
    }
}
