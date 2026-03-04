<?php

/**
 * Yak Shaver Inventory - lend status template helper
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

if (!function_exists('ysinventoryGetLendStatusLabels')) {
    /**
     * Return lend status language keys and badge styles keyed by status id.
     *
     * @return  array<int, array{0: string, 1: string}>
     */
    function ysinventoryGetLendStatusLabels(): array
    {
        return [
            1 => ['COM_YSINVENTORY_LEND_STATUS_REQUESTED', 'warning'],
            2 => ['COM_YSINVENTORY_LEND_STATUS_BORROWED', 'primary'],
            3 => ['COM_YSINVENTORY_LEND_STATUS_RETURNED', 'success'],
            4 => ['COM_YSINVENTORY_LEND_STATUS_LOST', 'danger'],
            5 => ['COM_YSINVENTORY_LEND_STATUS_RETURNED_DAMAGED', 'warning'],
            6 => ['COM_YSINVENTORY_LEND_STATUS_RETURNED_OVERDUE', 'danger'],
        ];
    }
}

