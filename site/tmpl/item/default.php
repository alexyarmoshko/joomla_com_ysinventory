<?php

/**
 * Yak Shaver Inventory — single item detail template (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \YakShaver\Component\Ysinventory\Site\View\Item\HtmlView $this */

$item = $this->item;

$statusLabels = [
    1 => 'COM_YSINVENTORY_LEND_STATUS_REQUESTED',
    2 => 'COM_YSINVENTORY_LEND_STATUS_BORROWED',
    3 => 'COM_YSINVENTORY_LEND_STATUS_RETURNED',
    4 => 'COM_YSINVENTORY_LEND_STATUS_LOST',
    5 => 'COM_YSINVENTORY_LEND_STATUS_RETURNED_DAMAGED',
    6 => 'COM_YSINVENTORY_LEND_STATUS_RETURNED_OVERDUE',
];

$assetStatusMapping = [
    '' => ['class' => 'secondary', 'text' => 'JNONE'],
    1 => ['class' => 'success', 'text' => 'COM_YSINVENTORY_ASSET_STATUS_IN_STOCK'],
    2 => ['class' => 'primary', 'text' => 'COM_YSINVENTORY_ASSET_STATUS_ON_LOAN'],
    3 => ['class' => 'warning', 'text' => 'COM_YSINVENTORY_ASSET_STATUS_MAINTENANCE'],
    4 => ['class' => 'danger', 'text' => 'COM_YSINVENTORY_ASSET_STATUS_LOST'],
];
$assetStatus = $assetStatusMapping[$item->ysi_status ?? ''] ?? $assetStatusMapping[''];
?>
<div class="com-ysinventory-item">
    <h2>
        <?php echo $this->escape($item->name); ?>
    </h2>

    <?php if (!empty($item->image)): ?>
        <div class="com-ysinventory-item__image mb-3">
            <img src="<?php echo $this->escape($item->image); ?>" alt="<?php echo $this->escape($item->name); ?>"
                class="img-fluid">
        </div>
    <?php endif; ?>

    <?php if (!empty($item->description)): ?>
        <div class="com-ysinventory-item__description mb-4">
            <?php echo $item->description; ?>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs" id="itemTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane"
                type="button" role="tab" aria-controls="details-pane" aria-selected="true">
                <?php echo Text::_('COM_YSINVENTORY_TAB_DETAILS'); ?>
            </button>
        </li>
        <?php if ($this->showBorrowings): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="borrowings-tab" data-bs-toggle="tab" data-bs-target="#borrowings-pane"
                    type="button" role="tab" aria-controls="borrowings-pane" aria-selected="false">
                    <?php echo Text::_('COM_YSINVENTORY_TAB_BORROWINGS'); ?>
                </button>
            </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content mt-3" id="itemTabContent">
        <div class="tab-pane fade show active" id="details-pane" role="tabpanel" aria-labelledby="details-tab">
            <div class="com-ysinventory-item__details">
                <table class="table">
                    <tbody>
                        <?php if (!empty($item->ysi_model)): ?>
                            <tr>
                                <th scope="row">
                                    <?php echo Text::_('COM_YSINVENTORY_FIELD_MODEL_LABEL'); ?>
                                </th>
                                <td>
                                    <?php echo $this->escape($item->ysi_model); ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!empty($item->ysi_serial_number)): ?>
                            <tr>
                                <th scope="row">
                                    <?php echo Text::_('COM_YSINVENTORY_FIELD_SERIAL_NUMBER_LABEL'); ?>
                                </th>
                                <td>
                                    <?php echo $this->escape($item->ysi_serial_number); ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!empty($item->ysi_sku)): ?>
                            <tr>
                                <th scope="row">
                                    <?php echo Text::_('COM_YSINVENTORY_FIELD_SKU_LABEL'); ?>
                                </th>
                                <td>
                                    <?php echo $this->escape($item->ysi_sku); ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <th scope="row">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_QUANTITY_LABEL'); ?>
                            </th>
                            <td>
                                <?php echo (int) $item->ysi_quantity; ?>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_ASSET_STATUS_LABEL'); ?>
                            </th>
                            <td>
                                <span class="badge bg-<?php echo $assetStatus['class']; ?>">
                                    <?php echo Text::_($assetStatus['text']); ?>
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_BRAND_LABEL'); ?>
                            </th>
                            <td>
                                <?php if (!empty($item->brand_id) && !empty($item->brand_name)): ?>
                                    <a
                                        href="<?php echo Route::_('index.php?option=com_ysinventory&view=brand&id=' . (int) $item->brand_id); ?>">
                                        <?php echo $this->escape($item->brand_name); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_CATEGORY_LABEL'); ?>
                            </th>
                            <td>
                                <?php if (!empty($item->catid) && !empty($item->category_title)): ?>
                                    <a
                                        href="<?php echo Route::_('index.php?option=com_ysinventory&view=category&id=' . (int) $item->catid); ?>">
                                        <?php echo $this->escape($item->category_title); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_INVENTORY_LABEL'); ?>
                            </th>
                            <td>
                                <?php echo $this->escape($item->inventory_name ?? ''); ?>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_LOCATION_LABEL'); ?>
                            </th>
                            <td>
                                <?php echo $this->escape($item->location_name ?? ''); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php if ($this->canRequestLend): ?>
                <div class="com-ysinventory-item__lend-request mt-4">
                    <h3>
                        <?php echo Text::_('COM_YSINVENTORY_LEND_REQUEST_TITLE'); ?>
                    </h3>
                    <form action="<?php echo Route::_('index.php?option=com_ysinventory&task=lend.request'); ?>"
                        method="post" class="row g-3">
                        <input type="hidden" name="ysi_item_id" value="<?php echo (int) $item->id; ?>">
                        <div class="col-md-3">
                            <label for="ysi_from" class="form-label">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_FROM_LABEL'); ?>
                            </label>
                            <input type="date" class="form-control" id="ysi_from" name="ysi_from" required>
                        </div>
                        <div class="col-md-3">
                            <label for="ysi_to" class="form-label">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_TO_LABEL'); ?>
                            </label>
                            <input type="date" class="form-control" id="ysi_to" name="ysi_to" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ysi_note" class="form-label">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_NOTE_LABEL'); ?>
                            </label>
                            <textarea class="form-control" id="ysi_note" name="ysi_note" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <?php echo Text::_('COM_YSINVENTORY_LEND_REQUEST_SUBMIT'); ?>
                            </button>
                        </div>
                        <?php echo HTMLHelper::_('form.token'); ?>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (!empty($item->tags)): ?>
                <div class="com-ysinventory-item__tags mt-3">
                    <h3>
                        <?php echo Text::_('COM_YSINVENTORY_ITEM_TAGS'); ?>
                    </h3>
                    <div>
                        <?php foreach ($item->tags as $tag): ?>
                            <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=tag&id=' . (int) $tag->id); ?>"
                                class="badge bg-secondary text-decoration-none me-1 mb-1">
                                <?php echo $this->escape($tag->group_name . ' — ' . $tag->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($this->showBorrowings): ?>
            <div class="tab-pane fade" id="borrowings-pane" role="tabpanel" aria-labelledby="borrowings-tab">
                <?php if (!empty($this->borrowings)): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_BORROWER'); ?></th>
                                <th><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_FROM'); ?></th>
                                <th><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_TO'); ?></th>
                                <th><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_STATUS'); ?></th>
                                <th><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_NOTE'); ?></th>
                                <th><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_CREATED'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->borrowings as $lend): ?>
                                <tr>
                                    <td><?php echo $this->escape($lend->borrower_name ?? ''); ?></td>
                                    <td><?php echo HTMLHelper::_('date', $lend->ysi_from, Text::_('DATE_FORMAT_LC4')); ?></td>
                                    <td><?php echo HTMLHelper::_('date', $lend->ysi_to, Text::_('DATE_FORMAT_LC4')); ?></td>
                                    <td><?php echo Text::_($statusLabels[(int) $lend->ysi_status] ?? ''); ?></td>
                                    <td><?php echo $this->escape($lend->ysi_note ?? ''); ?></td>
                                    <td><?php echo HTMLHelper::_('date', $lend->created, Text::_('DATE_FORMAT_LC4')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        <?php echo Text::_('COM_YSINVENTORY_NO_BORROWINGS'); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>