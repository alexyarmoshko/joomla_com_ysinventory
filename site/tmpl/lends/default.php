<?php

/**
 * Yak Shaver Inventory — loans list template (site)
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
use Joomla\CMS\Session\Session;
use YakShaver\Component\Ysinventory\Administrator\Helper\StatusHelper;

/** @var \YakShaver\Component\Ysinventory\Site\View\Lends\HtmlView $this */

$state = $this->state;
$isModerator = $this->isModerator;
$itemId = Factory::getApplication()->getInput()->getInt('Itemid');
$itemIdParam = $itemId > 0 ? '&Itemid=' . $itemId : '';

$statusLabels = StatusHelper::getLendStatusLabels();
?>
<div class="com-ysinventory-lends">
    <h2>
        <?php echo Text::_('COM_YSINVENTORY_LENDS'); ?>
    </h2>

    <?php if ($isModerator): ?>
        <div class="mb-3">
            <a href="<?php echo Route::_('index.php?option=com_ysinventory&task=lend.add'); ?>" class="btn btn-success">
                <?php echo Text::_('COM_YSINVENTORY_LEND_NEW'); ?>
            </a>
        </div>
    <?php endif; ?>

    <form action="<?php echo Route::_('index.php?option=com_ysinventory&view=lends'); ?>" method="get" name="adminForm"
        id="adminForm" class="com-ysinventory-lends__filter mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="filter_search">
                    <?php echo Text::_('COM_YSINVENTORY_FILTER_SEARCH_LENDS'); ?>
                </label>
                <input type="text" name="search" id="filter_search" class="form-control"
                    value="<?php echo $this->escape($state->get('filter.search', '')); ?>"
                    placeholder="<?php echo Text::_('COM_YSINVENTORY_FILTER_SEARCH_LENDS_HINT'); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="filter_ysi_item_id">
                    <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_ITEM_LABEL'); ?>
                </label>
                <select name="ysi_item_id" id="filter_ysi_item_id" class="form-select">
                    <option value="">
                        <?php echo Text::_('COM_YSINVENTORY_ALL_ASSETS'); ?>
                    </option>
                    <?php foreach ($this->assetFilterOptions as $assetOption): ?>
                        <option value="<?php echo (int) $assetOption->value; ?>" <?php echo ((string) $state->get('filter.ysi_item_id') === (string) $assetOption->value) ? ' selected' : ''; ?>>
                            <?php echo $this->escape($assetOption->text); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="filter_ysi_status">
                    <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_STATUS_LABEL'); ?>
                </label>
                <select name="ysi_status" id="filter_ysi_status" class="form-select">
                    <option value="">
                        <?php echo Text::_('COM_YSINVENTORY_ALL_STATUSES'); ?>
                    </option>
                    <?php foreach ($statusLabels as $value => $info): ?>
                        <option value="<?php echo (int) $value; ?>" <?php echo ((string) $state->get('filter.ysi_status') === (string) $value) ? ' selected' : ''; ?>>
                            <?php echo Text::_($info[0]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>
                </button>
            </div>
            <div class="col-auto">
                <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=lends'); ?>"
                    class="btn btn-secondary">
                    <?php echo Text::_('COM_YSINVENTORY_CLEAR_FILTERS'); ?>
                </a>
            </div>
        </div>
        <input type="hidden" name="option" value="com_ysinventory">
        <input type="hidden" name="view" value="lends">
    </form>

    <?php if (empty($this->items)): ?>
        <p class="alert alert-info">
            <?php echo Text::_('COM_YSINVENTORY_NO_LENDS'); ?>
        </p>
    <?php else: ?>
        <div class="com-ysinventory-lends__list">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>
                            <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_ITEM_LABEL'); ?>
                        </th>
                        <th>
                            <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_STATUS_LABEL'); ?>
                        </th>
                        <th class="d-none d-md-table-cell">
                            <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_FROM_LABEL'); ?>
                        </th>
                        <th class="d-none d-md-table-cell">
                            <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_TO_LABEL'); ?>
                        </th>
                        <th class="d-none d-md-table-cell">
                            <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_USER_LABEL'); ?>
                        </th>
                        <th class="d-none d-md-table-cell">
                            <?php echo Text::_('JGLOBAL_FIELD_CREATED_LABEL'); ?>
                        </th>
                        <?php if ($isModerator): ?>
                            <th class="text-end">
                                <?php echo Text::_('JACTION_EDIT'); ?>
                            </th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $item):
                        $statusInfo = $statusLabels[(int) $item->ysi_status] ?? ['JUNKNOWN', 'secondary'];
                        ?>
                        <tr>
                            <td>
                                <?php echo $this->escape($item->item_name ?? ''); ?>
                            </td>
                            <td>
                                <?php if ($isModerator): ?>
                                    <a href="<?php echo Route::_('index.php?option=com_ysinventory&task=lend.edit&id=' . (int) $item->id . $itemIdParam); ?>"
                                        class="text-decoration-none">
                                        <span class="badge bg-<?php echo $statusInfo[1]; ?>">
                                            <?php echo Text::_($statusInfo[0]); ?>
                                        </span>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-<?php echo $statusInfo[1]; ?>">
                                        <?php echo Text::_($statusInfo[0]); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('date', $item->ysi_from, Text::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('date', $item->ysi_to, Text::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo $this->escape($item->user_name ?? ''); ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <?php if ($isModerator): ?>
                                <td class="text-end">
                                    <a href="<?php echo Route::_('index.php?option=com_ysinventory&task=lend.edit&id=' . (int) $item->id . $itemIdParam); ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        <?php echo Text::_('JACTION_EDIT'); ?>
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="com-ysinventory-lends__pagination">
            <?php echo $this->pagination->getListFooter(); ?>
        </div>
    <?php endif; ?>
</div>
