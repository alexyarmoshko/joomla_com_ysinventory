<?php

/**
 * Yak Shaver Inventory — status mapping helper
 *
 * Provides lend status and asset status label/badge mappings
 * used across admin and site templates.
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Helper;

\defined('_JEXEC') or die;

class StatusHelper
{
    /**
     * Return lend status language keys and badge styles keyed by status id.
     *
     * @return  array<int, array{0: string, 1: string}>
     */
    public static function getLendStatusLabels(): array
    {
        return [
            1 => ['COM_YSINVENTORY_LEND_STATUS_REQUESTED', 'warning'],
            2 => ['COM_YSINVENTORY_LEND_STATUS_ON_LOAN', 'primary'],
            3 => ['COM_YSINVENTORY_LEND_STATUS_RETURNED', 'success'],
            4 => ['COM_YSINVENTORY_LEND_STATUS_LOST', 'danger'],
            5 => ['COM_YSINVENTORY_LEND_STATUS_RETURNED_DAMAGED', 'warning'],
            6 => ['COM_YSINVENTORY_LEND_STATUS_RETURNED_OVERDUE', 'danger'],
            7 => ['COM_YSINVENTORY_LEND_STATUS_CANCELLED', 'secondary'],
            8 => ['COM_YSINVENTORY_LEND_STATUS_DENIED', 'dark'],
        ];
    }

    /**
     * Return asset status language keys and badge styles keyed by status id.
     *
     * @return  array<int|string, array{class: string, text: string}>
     */
    public static function getAssetStatusMapping(): array
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
