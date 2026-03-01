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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \YakShaver\Component\Ysinventory\Site\View\Item\HtmlView $this */

$item = $this->item;
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
                        <?php echo Text::_('COM_YSINVENTORY_FIELD_BRAND_LABEL'); ?>
                    </th>
                    <td>
                        <?php if (!empty($item->brand_id)): ?>
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
                        <?php if (!empty($item->catid)): ?>
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