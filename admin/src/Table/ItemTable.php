<?php

/**
 * Yak Shaver Inventory — item table class
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;

class ItemTable extends Table implements CurrentUserInterface
{
    use CurrentUserTrait;

    protected $_supportNullValue = false;

    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_ysinventory.item';

        parent::__construct('#__ysi_items', 'id', $db, $dispatcher);

        $this->setColumnAlias('title', 'name');
    }

    public function store($updateNulls = true)
    {
        $date = Factory::getDate()->toSql();
        $userId = $this->getCurrentUser()->id;

        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->id) {
            $this->modified_by = $userId;
            $this->modified = $date;
        } else {
            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }

            if (!(int) $this->modified) {
                $this->modified = $date;
            }

            if (empty($this->modified_by)) {
                $this->modified_by = $userId;
            }
        }

        // Verify alias uniqueness.
        $table = new self($this->getDatabase(), $this->getDispatcher());

        if ($table->load(['alias' => $this->alias]) && ($table->id != $this->id || $this->id == 0)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_UNIQUE_ALIAS'));

            if ($table->published === -2) {
                $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_UNIQUE_ALIAS_TRASHED'));
            }

            return false;
        }

        return parent::store($updateNulls);
    }

    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (trim($this->name) == '') {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_PROVIDE_VALID_NAME'));

            return false;
        }

        $this->generateAlias();

        // Validate required FK references.
        if (empty($this->ysi_inventory_id)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_REQUIRE_INVENTORY'));

            return false;
        }

        if (empty($this->catid)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_REQUIRE_CATEGORY'));

            return false;
        }

        if (empty($this->brand_id)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_REQUIRE_BRAND'));

            return false;
        }

        if (empty($this->ysi_location_user_id)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_REQUIRE_LOCATION'));

            return false;
        }

        if ((int) $this->ysi_quantity < 1) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_QUANTITY_MIN'));

            return false;
        }

        // Validate that referenced inventory exists and is not trashed.
        $db = $this->getDatabase();

        $invId = (int) $this->ysi_inventory_id;
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__ysi_inventories'))
            ->where($db->quoteName('id') . ' = :invId')
            ->where($db->quoteName('published') . ' != -2')
            ->bind(':invId', $invId, ParameterType::INTEGER);
        $db->setQuery($query);

        if ((int) $db->loadResult() === 0) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_INVENTORY_NOT_FOUND'));

            return false;
        }

        // Validate that referenced category exists, is not trashed, and is not a root category.
        $catId = (int) $this->catid;
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__ysi_categories'))
            ->where($db->quoteName('id') . ' = :catId')
            ->where($db->quoteName('published') . ' != -2')
            ->where($db->quoteName('level') . ' > 0')
            ->bind(':catId', $catId, ParameterType::INTEGER);
        $db->setQuery($query);

        if ((int) $db->loadResult() === 0) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_CATEGORY_NOT_FOUND'));

            return false;
        }

        // Validate that referenced brand exists and is not trashed.
        $brandId = (int) $this->brand_id;
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__ysi_brands'))
            ->where($db->quoteName('id') . ' = :brandId')
            ->where($db->quoteName('published') . ' != -2')
            ->bind(':brandId', $brandId, ParameterType::INTEGER);
        $db->setQuery($query);

        if ((int) $db->loadResult() === 0) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_BRAND_NOT_FOUND'));

            return false;
        }

        // Validate that referenced location user exists.
        $locUserId = (int) $this->ysi_location_user_id;
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('id') . ' = :locUserId')
            ->bind(':locUserId', $locUserId, ParameterType::INTEGER);
        $db->setQuery($query);

        if ((int) $db->loadResult() === 0) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ITEM_LOCATION_NOT_FOUND'));

            return false;
        }

        return true;
    }

    public function generateAlias()
    {
        if (empty($this->alias)) {
            $this->alias = $this->name;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language ?? '');

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return $this->alias;
    }

    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
