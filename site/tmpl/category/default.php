<?php

/**
 * Yak Shaver Inventory — single category template (site)
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

/** @var \YakShaver\Component\Ysinventory\Site\View\Category\HtmlView $this */

$itemId = Factory::getApplication()->getInput()->getInt('Itemid');
$itemIdParam = $itemId > 0 ? '&Itemid=' . $itemId : '';
?>
<div class="com-ysinventory-category">
    <h2><?php echo $this->escape($this->category->title); ?></h2>

    <?php if (!empty($this->category->description)) : ?>
        <div class="com-ysinventory-category__description">
            <?php echo $this->category->description; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->children)) : ?>
        <div class="com-ysinventory-category__children mb-4">
            <h3><?php echo Text::_('COM_YSINVENTORY_SUBCATEGORIES'); ?></h3>
            <ul class="list-group">
                <?php foreach ($this->children as $child) : ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=category&id=' . (int) $child->id); ?>">
                            <?php echo $this->escape($child->title); ?>
                        </a>
                        <span class="badge bg-secondary rounded-pill"><?php echo (int) $child->item_count; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="com-ysinventory-category__items">
        <h3><?php echo Text::_('COM_YSINVENTORY_ITEMS_IN_CATEGORY'); ?></h3>

        <?php if (empty($this->items)) : ?>
            <p class="alert alert-info"><?php echo Text::_('COM_YSINVENTORY_NO_ITEMS_IN_CATEGORY'); ?></p>
        <?php else : ?>
            <ul class="list-group">
                <?php foreach ($this->items as $item) : ?>
                    <li class="list-group-item">
                        <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=item&id=' . (int) $item->id . $itemIdParam); ?>">
                            <?php echo $this->escape($item->name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php echo $this->pagination->getListFooter(); ?>
        <?php endif; ?>
    </div>
</div>
