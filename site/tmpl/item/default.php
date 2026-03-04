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

use Joomla\CMS\Factory;
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
$activeTab = $this->activeTab ?? 'details';
$itemId = Factory::getApplication()->getInput()->getInt('Itemid');
$itemIdParam = $itemId > 0 ? '&Itemid=' . $itemId : '';
?>
<div class="com-ysinventory-item">

    <h2>
        <?php echo $this->escape($item->name); ?>
    </h2>

    <?php if (!empty($item->image) || !empty($item->description)): ?>
        <div class="row g-3 align-items-start mb-3">
            <?php if (!empty($item->image)): ?>
                <div class="<?php echo !empty($item->description) ? 'col-md-4 col-lg-3' : 'col-12'; ?>">
                    <div class="com-ysinventory-item__image mb-0">
                        <img src="<?php echo $this->escape($item->image); ?>" alt="<?php echo $this->escape($item->name); ?>"
                            class="img-fluid">
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($item->description)): ?>
                <div class="<?php echo !empty($item->image) ? 'col-md-8 col-lg-9' : 'col-12'; ?>" style="background-color: transparent !important;">
                    <div class="com-ysinventory-item-description">
                        <?php echo $item->description; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs" id="itemTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link<?php echo $activeTab === 'details' ? ' active' : ''; ?>" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane"
                type="button" role="tab" aria-controls="details-pane" aria-selected="<?php echo $activeTab === 'details' ? 'true' : 'false'; ?>">
                <?php echo Text::_('COM_YSINVENTORY_TAB_DETAILS'); ?>
            </button>
        </li>
        <?php if ($this->canRequestLend): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link<?php echo $activeTab === 'loan' ? ' active' : ''; ?>" id="loan-tab" data-bs-toggle="tab" data-bs-target="#loan-pane"
                    type="button" role="tab" aria-controls="loan-pane" aria-selected="<?php echo $activeTab === 'loan' ? 'true' : 'false'; ?>">
                    <?php echo Text::_('COM_YSINVENTORY_TAB_LOAN'); ?>
                </button>
            </li>
        <?php endif; ?>
        <?php if ($this->showBorrowings): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link<?php echo $activeTab === 'borrowings' ? ' active' : ''; ?>" id="borrowings-tab" data-bs-toggle="tab" data-bs-target="#borrowings-pane"
                    type="button" role="tab" aria-controls="borrowings-pane" aria-selected="<?php echo $activeTab === 'borrowings' ? 'true' : 'false'; ?>">
                    <?php echo Text::_('COM_YSINVENTORY_TAB_BORROWINGS'); ?>
                </button>
            </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content mt-3" id="itemTabContent">
        <div class="tab-pane fade<?php echo $activeTab === 'details' ? ' show active' : ''; ?>" id="details-pane" role="tabpanel" aria-labelledby="details-tab">
            <div class="com-ysinventory-item__details">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <?php if (!empty($item->catid) && !empty($item->category_title)): ?>
                                <dt class="col-sm-5">
                                    <?php echo Text::_('COM_YSINVENTORY_FIELD_CATEGORY_LABEL'); ?>
                                </dt>
                                <dd class="col-sm-7">
                                    <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=category&id=' . (int) $item->catid); ?>">
                                        <?php echo $this->escape($item->category_title); ?>
                                    </a>
                                </dd>
                            <?php endif; ?>

                            <dt class="col-sm-5">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_BRAND_LABEL'); ?>
                            </dt>
                            <dd class="col-sm-7">
                                <?php if (!empty($item->brand_id) && !empty($item->brand_name)): ?>
                                    <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=brand&id=' . (int) $item->brand_id); ?>">
                                        <?php echo $this->escape($item->brand_name); ?>
                                    </a>
                                <?php else: ?>
                                    &nbsp;
                                <?php endif; ?>
                            </dd>

                            <?php if (!empty($item->ysi_model)): ?>
                                <dt class="col-sm-5">
                                    <?php echo Text::_('COM_YSINVENTORY_FIELD_MODEL_LABEL'); ?>
                                </dt>
                                <dd class="col-sm-7">
                                    <?php echo $this->escape($item->ysi_model); ?>
                                </dd>
                            <?php endif; ?>

                            <?php if (!empty($item->ysi_serial_number)): ?>
                                <dt class="col-sm-5">
                                    <?php echo Text::_('COM_YSINVENTORY_FIELD_SERIAL_NUMBER_SHORT_LABEL'); ?>
                                </dt>
                                <dd class="col-sm-7">
                                    <?php echo $this->escape($item->ysi_serial_number); ?>
                                </dd>
                            <?php endif; ?>

                            <?php if (!empty($item->ysi_sku)): ?>
                                <dt class="col-sm-5">
                                    <?php echo Text::_('COM_YSINVENTORY_FIELD_SKU_LABEL'); ?>
                                </dt>
                                <dd class="col-sm-7">
                                    <?php echo $this->escape($item->ysi_sku); ?>
                                </dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_INVENTORY_LABEL'); ?>
                            </dt>
                            <dd class="col-sm-7">
                                <?php echo $this->escape($item->inventory_name ?? ''); ?>
                            </dd>

                            <dt class="col-sm-5">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_LOCATION_LABEL'); ?>
                            </dt>
                            <dd class="col-sm-7">
                                <?php echo $this->escape($item->location_name ?? ''); ?>
                            </dd>

                            <dt class="col-sm-5">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_QUANTITY_LABEL'); ?>
                            </dt>
                            <dd class="col-sm-7">
                                <?php echo (int) $item->ysi_quantity; ?>
                            </dd>

                            <dt class="col-sm-5">
                                <?php echo Text::_('COM_YSINVENTORY_FIELD_ASSET_STATUS_LABEL'); ?>
                            </dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-<?php echo $assetStatus['class']; ?>">
                                    <?php echo Text::_($assetStatus['text']); ?>
                                </span>
                            </dd>
                        </dl>

                    </div>
                </div>
            </div>

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

        <?php if ($this->canRequestLend): ?>
            <div class="tab-pane fade<?php echo $activeTab === 'loan' ? ' show active' : ''; ?>" id="loan-pane" role="tabpanel" aria-labelledby="loan-tab">
                <div class="com-ysinventory-item__lend-request mt-2">
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
                        <div class="col-12">
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
            </div>
        <?php endif; ?>

        <?php if ($this->showBorrowings): ?>
            <div class="tab-pane fade<?php echo $activeTab === 'borrowings' ? ' show active' : ''; ?>" id="borrowings-pane" role="tabpanel" aria-labelledby="borrowings-tab">
                <?php if (!empty($this->borrowings)): ?>
                    <form action="<?php echo Route::_('index.php?option=com_ysinventory&view=item&id=' . (int) $item->id . $itemIdParam); ?>"
                        method="get" name="borrowingsForm" id="borrowingsForm">
                        <table class="table table-striped" id="borrowingsTable">
                            <thead>
                                <tr>
                                    <th><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_STATUS'); ?></th>
                                    <th class="d-none d-md-table-cell"><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_FROM'); ?></th>
                                    <th class="d-none d-md-table-cell"><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_TO'); ?></th>
                                    <th><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_BORROWER'); ?></th>
                                    <th class="d-none d-md-table-cell"><?php echo Text::_('COM_YSINVENTORY_BORROWINGS_CREATED'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($this->borrowings as $lend): ?>
                                    <tr>
                                        <td><?php echo Text::_($statusLabels[(int) $lend->ysi_status] ?? ''); ?></td>
                                        <td class="d-none d-md-table-cell"><?php echo HTMLHelper::_('date', $lend->ysi_from, Text::_('DATE_FORMAT_LC4')); ?></td>
                                        <td class="d-none d-md-table-cell"><?php echo HTMLHelper::_('date', $lend->ysi_to, Text::_('DATE_FORMAT_LC4')); ?></td>
                                        <td><?php echo $this->escape($lend->borrower_name ?? ''); ?></td>
                                        <td class="d-none d-md-table-cell"><?php echo HTMLHelper::_('date', $lend->created, Text::_('DATE_FORMAT_LC4')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if ($this->borrowingsPagination): ?>
                            <?php echo $this->borrowingsPagination->getListFooter(); ?>
                        <?php endif; ?>

                        <input type="hidden" name="option" value="com_ysinventory">
                        <input type="hidden" name="view" value="item">
                        <input type="hidden" name="id" value="<?php echo (int) $item->id; ?>">
                        <input type="hidden" name="active_tab" value="borrowings">
                        <?php if ($itemId > 0): ?>
                            <input type="hidden" name="Itemid" value="<?php echo (int) $itemId; ?>">
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        <?php echo Text::_('COM_YSINVENTORY_NO_BORROWINGS'); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
