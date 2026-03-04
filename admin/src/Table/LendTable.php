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
use Joomla\CMS\Log\Log;
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
        0 => [1],            // new record => Requested
        1 => [2],            // Requested => On Loan
        2 => [3, 4, 5, 6],  // On Loan => Returned | Lost | Returned Damaged | Returned Overdue
    ];

    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_ysinventory.lend';

        parent::__construct('#__ysi_lends', 'id', $db, $dispatcher);
    }

    public function store($updateNulls = true)
    {
        $isUpdate = (bool) $this->id;

        // Journal: capture old row snapshot before update (written only after success).
        $journalPayload = null;

        if ($isUpdate) {
            $journalPayload = $this->prepareJournalEntry((int) $this->id, 'U');
        }

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

        $status = (int) $this->ysi_status;
        $needsStockGuard = ($status === 2 && $this->oldStatus !== 2);

        if (!$needsStockGuard) {
            $result = parent::store($updateNulls);

            if ($result && $journalPayload) {
                $this->commitJournalEntry($journalPayload);
            }

            return $result;
        }

        // Transactional stock guard with row-level lock (Finding 7).
        $db = $this->getDatabase();
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

            if ($journalPayload) {
                $this->commitJournalEntry($journalPayload);
            }

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

        $fromDate = $this->normalizeDateValue((string) $this->ysi_from);
        $toDate = $this->normalizeDateValue((string) $this->ysi_to);

        if ($fromDate === null) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_INVALID_DATE_FORMAT'));

            return false;
        }

        if ($toDate === null) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_INVALID_DATE_FORMAT'));

            return false;
        }

        // Persist normalized DATE values to match schema and avoid timezone-formatted strings.
        $this->ysi_from = $fromDate;
        $this->ysi_to = $toDate;

        if ($this->ysi_from >= $this->ysi_to) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_LEND_DATE_ORDER'));

            return false;
        }

        // Status validation.
        $status = (int) $this->ysi_status;

        if (!\in_array($status, [1, 2, 3, 4, 5, 6], true)) {
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

    public function delete($pk = null)
    {
        $pk = $pk ?: $this->{$this->_tbl_key};

        // Journal: capture old row snapshot before delete (written only after success).
        $journalPayload = null;

        if ($pk) {
            $journalPayload = $this->prepareJournalEntry((int) $pk, 'D');
        }

        $result = parent::delete($pk);

        if ($result && $journalPayload) {
            $this->commitJournalEntry($journalPayload);
        }

        return $result;
    }

    public function getTypeAlias()
    {
        return $this->typeAlias;
    }

    /**
     * Capture the pre-change lend row and related human-readable fields.
     *
     * Returns an associative array ready for commitJournalEntry(), or null
     * when the source row cannot be loaded.
     */
    private function prepareJournalEntry(int $lendId, string $operation): ?array
    {
        $db = $this->getDatabase();

        // Load the current (pre-change) lend row.
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__ysi_lends'))
            ->where($db->quoteName('id') . ' = :lendId')
            ->bind(':lendId', $lendId, ParameterType::INTEGER);
        $db->setQuery($query);
        $oldRow = $db->loadAssoc();

        if (empty($oldRow)) {
            return null;
        }

        // Resolve human-readable message fields.
        $itemId = (int) ($oldRow['ysi_item_id'] ?? 0);
        $loaneeId = (int) ($oldRow['ysi_user_id'] ?? 0);

        $loaneeUsername = '';
        $assetName = '';
        $assetId = '';
        $assetSerialNumber = '';

        if ($loaneeId > 0) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('username'))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('id') . ' = :uid')
                ->bind(':uid', $loaneeId, ParameterType::INTEGER);
            $db->setQuery($query);
            $loaneeUsername = (string) $db->loadResult();
        }

        if ($itemId > 0) {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('name'),
                    $db->quoteName('ysi_sku'),
                    $db->quoteName('ysi_serial_number'),
                ])
                ->from($db->quoteName('#__ysi_items'))
                ->where($db->quoteName('id') . ' = :iid')
                ->bind(':iid', $itemId, ParameterType::INTEGER);
            $db->setQuery($query);
            $itemRow = $db->loadAssoc();

            if ($itemRow) {
                $assetName = (string) ($itemRow['name'] ?? '');
                $assetId = (string) ($itemRow['ysi_sku'] ?? '');
                $assetSerialNumber = (string) ($itemRow['ysi_serial_number'] ?? '');
            }
        }

        return [
            'operation'      => $operation,
            'oldRow'         => $oldRow,
            'loaneeUsername'  => $loaneeUsername,
            'assetName'      => $assetName,
            'assetId'        => $assetId,
            'serialNumber'   => $assetSerialNumber,
        ];
    }

    /**
     * Insert a prepared journal entry into #__ysi_lends_log.
     *
     * Must only be called after the primary operation has succeeded.
     */
    private function commitJournalEntry(array $payload): void
    {
        $db = $this->getDatabase();

        // Flat merge: old row fields + resolved human-readable fields, no nesting.
        $merged = $payload['oldRow'];
        $merged['loanee_username']     = $payload['loaneeUsername'];
        $merged['asset_name']          = $payload['assetName'];
        $merged['asset_id']            = $payload['assetId'];
        $merged['asset_serial_number'] = $payload['serialNumber'];

        $message = json_encode($merged, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);

        $logDate = Factory::getDate()->toSql();
        $actorId = (int) $this->getCurrentUser()->id;
        $operation = $payload['operation'];

        $columns = [
            'user_id',
            'log_date',
            'operation',
            'message',
        ];

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__ysi_lends_log'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', [
                ':actorId',
                ':logDate',
                ':op',
                ':message',
            ]))
            ->bind(':actorId', $actorId, ParameterType::INTEGER)
            ->bind(':logDate', $logDate)
            ->bind(':op', $operation)
            ->bind(':message', $message);

        try {
            $db->setQuery($query);
            $db->execute();
        } catch (\Exception $e) {
            // Journal write failure must not block the primary operation.
            // Log detail for debugging; show generic message to user.
            Log::add(
                'Journal write failed: ' . $e->getMessage(),
                Log::WARNING,
                'com_ysinventory'
            );
            Factory::getApplication()->enqueueMessage(
                Text::_('COM_YSINVENTORY_WARNING_JOURNAL_WRITE_FAILED'),
                'warning'
            );
        }
    }

    private function normalizeDateValue(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        // Already normalized date.
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if ($date && $date->format('Y-m-d') === $value) {
            return $value;
        }

        // Accept calendar field variants that include time, keeping only the DATE part.
        if (!preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $matches)) {
            return null;
        }

        $normalized = $matches[1];
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if (!$date || $date->format('Y-m-d') !== $normalized) {
            return null;
        }

        return $normalized;
    }
}
