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
            2 => ['COM_YSINVENTORY_LEND_STATUS_ON_LOAN', 'primary'],
            3 => ['COM_YSINVENTORY_LEND_STATUS_RETURNED', 'success'],
            4 => ['COM_YSINVENTORY_LEND_STATUS_LOST', 'danger'],
            5 => ['COM_YSINVENTORY_LEND_STATUS_RETURNED_DAMAGED', 'warning'],
            6 => ['COM_YSINVENTORY_LEND_STATUS_RETURNED_OVERDUE', 'danger'],
        ];
    }
}

if (!function_exists('ysinventoryGetAssetStatusMapping')) {
    /**
     * Return asset status language keys and badge styles keyed by status id.
     *
     * @return  array<int|string, array{class: string, text: string}>
     */
    function ysinventoryGetAssetStatusMapping(): array
    {
        return [
            '' => ['class' => 'secondary', 'text' => 'JNONE'],
            1 => ['class' => 'success', 'text' => 'COM_YSINVENTORY_ASSET_STATUS_IN_STOCK'],
            2 => ['class' => 'primary', 'text' => 'COM_YSINVENTORY_ASSET_STATUS_ON_LOAN'],
            3 => ['class' => 'warning', 'text' => 'COM_YSINVENTORY_ASSET_STATUS_MAINTENANCE'],
            4 => ['class' => 'danger', 'text' => 'COM_YSINVENTORY_ASSET_STATUS_LOST'],
        ];
    }
}
