<?php

/**
 * Yak Shaver Inventory — items list template (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \YakShaver\Component\Ysinventory\Site\View\Items\HtmlView $this */

$filterOptions = $this->filterOptions;
$state = $this->state;
$itemId = Factory::getApplication()->getInput()->getInt('Itemid');
$itemIdParam = $itemId > 0 ? '&Itemid=' . $itemId : '';
?>
<div class="com-ysinventory-items">
    <h2>
        <?php echo Text::_('COM_YSINVENTORY_ITEMS'); ?>
    </h2>

    <form action="<?php echo Route::_('index.php?option=com_ysinventory&view=items'); ?>" method="get" name="adminForm"
        id="adminForm" class="com-ysinventory-items__filter mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="filter_search">
                    <?php echo Text::_('COM_YSINVENTORY_FILTER_SEARCH_ITEMS'); ?>
                </label>
                <input type="text" name="search" id="filter_search" class="form-control"
                    value="<?php echo $this->escape($state->get('filter.search', '')); ?>"
                    placeholder="<?php echo Text::_('COM_YSINVENTORY_FILTER_SEARCH_ITEMS_HINT'); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="filter_brand">
                    <?php echo Text::_('COM_YSINVENTORY_FIELD_BRAND_LABEL'); ?>
                </label>
                <select name="brand" id="filter_brand" class="form-select">
                    <option value="">
                        <?php echo Text::_('COM_YSINVENTORY_ALL_BRANDS'); ?>
                    </option>
                    <?php foreach ($filterOptions['brands'] as $br): ?>
                        <option value="<?php echo (int) $br->id; ?>" <?php echo ((int) $state->get('filter.brand') === (int) $br->id) ? ' selected' : ''; ?>>
                            <?php echo $this->escape($br->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="filter_tag">
                    <?php echo Text::_('COM_YSINVENTORY_FIELD_TAG_LABEL'); ?>
                </label>
                <select name="tag" id="filter_tag" class="form-select">
                    <option value="">
                        <?php echo Text::_('COM_YSINVENTORY_ALL_TAGS'); ?>
                    </option>
                    <?php foreach ($filterOptions['tags'] as $tag): ?>
                        <option value="<?php echo (int) $tag->id; ?>" <?php echo ((int) $state->get('filter.tag') === (int) $tag->id) ? ' selected' : ''; ?>>
                            <?php echo $this->escape($tag->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row g-2 mt-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="filter_catid">
                    <?php echo Text::_('COM_YSINVENTORY_FIELD_CATEGORY_LABEL'); ?>
                </label>
                <select name="catid" id="filter_catid" class="form-select">
                    <option value="">
                        <?php echo Text::_('COM_YSINVENTORY_ALL_CATEGORIES'); ?>
                    </option>
                    <?php foreach ($filterOptions['categories'] as $cat): ?>
                        <option value="<?php echo (int) $cat->id; ?>" <?php echo ((int) $state->get('filter.category') === (int) $cat->id) ? ' selected' : ''; ?>>
                            <?php echo $this->escape($cat->title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="filter_inventory">
                    <?php echo Text::_('COM_YSINVENTORY_FIELD_INVENTORY_LABEL'); ?>
                </label>
                <select name="inventory" id="filter_inventory" class="form-select">
                    <option value="">
                        <?php echo Text::_('COM_YSINVENTORY_ALL_INVENTORIES'); ?>
                    </option>
                    <?php foreach ($filterOptions['inventories'] as $inv): ?>
                        <option value="<?php echo (int) $inv->id; ?>" <?php echo ((int) $state->get('filter.inventory') === (int) $inv->id) ? ' selected' : ''; ?>>
                            <?php echo $this->escape($inv->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="filter_location">
                    <?php echo Text::_('COM_YSINVENTORY_FIELD_LOCATION_LABEL'); ?>
                </label>
                <select name="location" id="filter_location" class="form-select">
                    <option value="">
                        <?php echo Text::_('COM_YSINVENTORY_ALL_LOCATIONS'); ?>
                    </option>
                    <?php foreach ($filterOptions['locations'] as $loc): ?>
                        <option value="<?php echo (int) $loc->id; ?>" <?php echo ((int) $state->get('filter.location') === (int) $loc->id) ? ' selected' : ''; ?>>
                            <?php echo $this->escape($loc->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary" aria-label="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
                    <span class="fa fa-search" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></span>
                </button>
            </div>
            <div class="col-auto">
                <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=items'); ?>"
                    class="btn btn-secondary">
                    <?php echo Text::_('COM_YSINVENTORY_CLEAR_FILTERS'); ?>
                </a>
            </div>
        </div>
        <input type="hidden" name="option" value="com_ysinventory">
        <input type="hidden" name="view" value="items">
    </form>

    <?php if (empty($this->items)): ?>
        <p class="alert alert-info">
            <?php echo Text::_('COM_YSINVENTORY_NO_ITEMS'); ?>
        </p>
    <?php else: ?>
        <div class="com-ysinventory-items__list">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>
                            <?php echo Text::_('COM_YSINVENTORY_FIELD_NAME_LABEL'); ?>
                        </th>
                        <th class="d-none d-md-table-cell">
                            <?php echo Text::_('COM_YSINVENTORY_FIELD_BRAND_LABEL'); ?>
                        </th>
                        <th class="d-none d-md-table-cell">
                            <?php echo Text::_('COM_YSINVENTORY_FIELD_CATEGORY_LABEL'); ?>
                        </th>
                        <th class="d-none d-md-table-cell">
                            <?php echo Text::_('COM_YSINVENTORY_FIELD_LOCATION_LABEL'); ?>
                        </th>
                        <th class="text-center">
                            <?php echo Text::_('COM_YSINVENTORY_FIELD_QUANTITY_LABEL'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $item): ?>
                        <tr>
                            <td>
                                <a
                                    href="<?php echo Route::_('index.php?option=com_ysinventory&view=item&id=' . (int) $item->id . $itemIdParam); ?>">
                                    <?php echo $this->escape($item->name); ?>
                                </a>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo $this->escape($item->brand_name ?? ''); ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo $this->escape($item->category_title ?? ''); ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo $this->escape($item->location_name ?? ''); ?>
                            </td>
                            <td class="text-center">
                                <?php echo (int) $item->ysi_quantity; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="com-ysinventory-items__pagination">
            <?php echo $this->pagination->getListFooter(); ?>
        </div>
    <?php endif; ?>
</div>
