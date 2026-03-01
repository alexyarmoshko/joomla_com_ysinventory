<?php

/**
 * Yak Shaver Inventory — inventory table class
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

class InventoryTable extends Table implements CurrentUserInterface
{
    use CurrentUserTrait;

    protected $_supportNullValue = false;

    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_ysinventory.inventory';

        parent::__construct('#__ysi_inventories', 'id', $db, $dispatcher);

        $this->setColumnAlias('title', 'name');
    }

    public function store($updateNulls = true)
    {
        $date   = Factory::getDate()->toSql();
        $userId = $this->getCurrentUser()->id;

        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->id) {
            $this->modified_by = $userId;
            $this->modified    = $date;
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
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_UNIQUE_ALIAS'));

            if ($table->published === -2) {
                $this->setError(Text::_('COM_YSINVENTORY_ERROR_UNIQUE_ALIAS_TRASHED'));
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

        $contactUserId    = (int) ($this->ysi_contact_user_id ?? 0);
        $contactContactId = (int) ($this->ysi_contact_contact_id ?? 0);
        $legacyContactId  = (int) ($this->ysi_contact_id ?? 0);

        // Backward compatibility for rows created before XOR contact migration.
        if ($contactUserId === 0 && $contactContactId === 0 && $legacyContactId > 0) {
            $contactUserId = $legacyContactId;
        }

        $this->ysi_contact_user_id    = $contactUserId;
        $this->ysi_contact_contact_id = $contactContactId;
        $this->ysi_contact_id         = $contactUserId;

        $hasUserOwner    = $contactUserId > 0;
        $hasContactOwner = $contactContactId > 0;

        if ($hasUserOwner === $hasContactOwner) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_CONTACT_XOR_REQUIRED'));

            return false;
        }

        $db = $this->getDatabase();

        if ($hasUserOwner) {
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $contactUserId, ParameterType::INTEGER);
            $db->setQuery($query);

            if ((int) $db->loadResult() === 0) {
                $this->setError(Text::_('COM_YSINVENTORY_ERROR_CONTACT_USER_NOT_FOUND'));

                return false;
            }
        }

        if ($hasContactOwner) {
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__contact_details'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $contactContactId, ParameterType::INTEGER);
            $db->setQuery($query);

            if ((int) $db->loadResult() === 0) {
                $this->setError(Text::_('COM_YSINVENTORY_ERROR_CONTACT_CONTACT_NOT_FOUND'));

                return false;
            }
        }

        if (empty($this->id)) {
            $this->hits = 0;
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
