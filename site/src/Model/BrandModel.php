<?php

/**
 * Yak Shaver Inventory — single brand model (site)
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

class BrandModel extends ListModel
{
    protected $brand;

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

        $brandId = $app->getInput()->getInt('id', 0);
        $this->setState('brand.id', $brandId);

        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('brand.id');

        return parent::getStoreId($id);
    }

    public function getBrand()
    {
        if ($this->brand !== null) {
            return $this->brand;
        }

        $brandId = (int) $this->getState('brand.id');

        if ($brandId <= 0) {
            $this->brand = false;

            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName([
                'id',
                'name',
                'alias',
                'description',
                'image',
                'published',
            ]))
            ->from($db->quoteName('#__ysi_brands'))
            ->where($db->quoteName('id') . ' = :id')
            ->where($db->quoteName('published') . ' = 1')
            ->bind(':id', $brandId, ParameterType::INTEGER);

        $db->setQuery($query);
        $this->brand = $db->loadObject();

        return $this->brand;
    }

    protected function getListQuery()
    {
        $brandId = (int) $this->getState('brand.id');
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName([
            'i.id',
            'i.name',
            'i.alias',
            'i.brand_id',
            'i.published',
            'i.ordering',
        ]));
        $query->from($db->quoteName('#__ysi_items', 'i'));
        $query->where($db->quoteName('i.brand_id') . ' = :brandId');
        $query->where($db->quoteName('i.published') . ' = 1');
        $query->bind(':brandId', $brandId, ParameterType::INTEGER);

        // Access filter on items.
        $user = Factory::getApplication()->getIdentity();
        $query->whereIn($db->quoteName('i.access'), $user->getAuthorisedViewLevels());

        $orderCol = $this->state->get('list.ordering', 'i.ordering');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
