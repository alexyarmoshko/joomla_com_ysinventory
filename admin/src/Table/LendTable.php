<?php

/**
 * Yak Shaver Inventory — lend table class
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;

class LendTable extends Table implements CurrentUserInterface
{
    use CurrentUserTrait;

    protected $_supportNullValue = true;

    /** @var int Status before save, determined in check(). */
    private $oldStatus = 0;

    /** @var array Valid status transitions: old => [allowed new statuses] */
    private const TRANSITIONS = [
        0 => [1],        // new record => Requested
        1 => [2],        // Requested => On Loan
        2 => [3, 4],     // On Loan => Returned | Lost
    ];

    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_ysinventory.lend';

        parent::__construct('#__ysi_lends', 'id', $db, $dispatcher);
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

        $status = (int) $this->ysi_status;
        $needsStockGuard = ($status === 2 && $this->oldStatus !== 2);

        if (!$needsStockGuard) {
            return parent::store($updateNulls);
        }

        // Transactional stock guard with row-level lock (Finding 7).
        $db     = $this->getDatabase();
        $itemId = (int) $this->ysi_item_id;
        $lendId = (int) $this->id;

        $db->transactionStart();

        try {
            // Lock the item row to prevent concurrent approvals.
            // Raw SQL with integer-cast value to safely append FOR UPDATE.
            $db->setQuery(
                'SELECT ' . $db->quoteName('ysi_quantity')
                . ' FROM ' . $db->quoteName('#__ysi_items')
                . ' WHERE ' . $db->quoteName('id') . ' = ' . $itemId
                . ' FOR UPDATE'
            );
            $quantity = (int) $db->loadResult();

            // Count currently loaned items (exclude this record if updating).
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__ysi_lends'))
                ->where($db->quoteName('ysi_item_id') . ' = :guardItemId')
                ->where($db->quoteName('ysi_status') . ' = 2')
                ->bind(':guardItemId', $itemId, ParameterType::INTEGER);

            if ($lendId > 0) {
                $query->where($db->quoteName('id') . ' != :guardLendId')
                    ->bind(':guardLendId', $lendId, ParameterType::INTEGER);
            }

            $db->setQuery($query);
            $borrowedCount = (int) $db->loadResult();

            if ($borrowedCount >= $quantity) {
                $db->transactionRollback();
                $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_NO_STOCK'));

                return false;
            }

            $result = parent::store($updateNulls);

            if (!$result) {
                $db->transactionRollback();

                return false;
            }

            $db->transactionCommit();

            return true;
        } catch (\Exception $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Required fields.
        if (empty($this->ysi_item_id)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_REQUIRE_ITEM'));

            return false;
        }

        if (empty($this->ysi_user_id)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_REQUIRE_USER'));

            return false;
        }

        // Date validation: strict format check with round-trip.
        if (empty($this->ysi_from) || empty($this->ysi_to)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_REQUIRE_DATES'));

            return false;
        }

        $fromDate = \DateTimeImmutable::createFromFormat('Y-m-d', $this->ysi_from);
        $toDate   = \DateTimeImmutable::createFromFormat('Y-m-d', $this->ysi_to);

        if (!$fromDate || $fromDate->format('Y-m-d') !== $this->ysi_from) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_INVALID_DATE_FORMAT'));

            return false;
        }

        if (!$toDate || $toDate->format('Y-m-d') !== $this->ysi_to) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_INVALID_DATE_FORMAT'));

            return false;
        }

        if ($this->ysi_from >= $this->ysi_to) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_DATE_ORDER'));

            return false;
        }

        // Status validation.
        $status = (int) $this->ysi_status;

        if (!\in_array($status, [1, 2, 3, 4], true)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_INVALID_STATUS'));

            return false;
        }

        // Status transition validation.
        $this->oldStatus = 0;

        if ($this->id) {
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('ysi_status'))
                ->from($db->quoteName('#__ysi_lends'))
                ->where($db->quoteName('id') . ' = :lendId')
                ->bind(':lendId', $this->id, ParameterType::INTEGER);
            $db->setQuery($query);
            $this->oldStatus = (int) $db->loadResult();
        }

        $oldStatus = $this->oldStatus;

        // Allow saving without changing the status.
        if ($status !== $oldStatus) {
            $allowed = self::TRANSITIONS[$oldStatus] ?? [];

            if (!\in_array($status, $allowed, true)) {
                $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_INVALID_TRANSITION'));

                return false;
            }
        }

        // Validate that referenced item exists and is published.
        $db = $this->getDatabase();
        $itemId = (int) $this->ysi_item_id;

        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('ysi_quantity')])
            ->from($db->quoteName('#__ysi_items'))
            ->where($db->quoteName('id') . ' = :itemId')
            ->where($db->quoteName('published') . ' = 1')
            ->bind(':itemId', $itemId, ParameterType::INTEGER);
        $db->setQuery($query);
        $item = $db->loadObject();

        if (!$item) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_ITEM_NOT_FOUND'));

            return false;
        }

        // Validate that referenced user exists.
        $userId = (int) $this->ysi_user_id;
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('id') . ' = :userId')
            ->bind(':userId', $userId, ParameterType::INTEGER);
        $db->setQuery($query);

        if ((int) $db->loadResult() === 0) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_USER_NOT_FOUND'));

            return false;
        }

        return true;
    }

    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
