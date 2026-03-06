<?php

/**
 * Yak Shaver Inventory — single tag template (site)
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

/** @var \YakShaver\Component\Ysinventory\Site\View\Tag\HtmlView $this */

$itemId = Factory::getApplication()->getInput()->getInt('Itemid');
$itemIdParam = $itemId > 0 ? '&Itemid=' . $itemId : '';
?>
<div class="com-ysinventory-tag">
    <h2>
        <?php echo $this->escape($this->tag->name); ?>
    </h2>

    <?php if (!empty($this->tag->tag_group_name)): ?>
        <div class="com-ysinventory-tag__group text-muted mb-2">
            <?php echo Text::sprintf('COM_YSINVENTORY_TAG_IN_GROUP', $this->escape($this->tag->tag_group_name)); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->tag->description)): ?>
        <div class="com-ysinventory-tag__description mb-4">
            <?php echo $this->tag->description; ?>
        </div>
    <?php endif; ?>

    <div class="com-ysinventory-tag__items">
        <h3>
            <?php echo Text::_('COM_YSINVENTORY_ITEMS_IN_TAG'); ?>
        </h3>

        <?php if (empty($this->items)): ?>
            <p class="alert alert-info">
                <?php echo Text::_('COM_YSINVENTORY_NO_ITEMS_IN_TAG'); ?>
            </p>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($this->items as $item): ?>
                    <li class="list-group-item">
                        <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=item&id=' . (int) $item->id . '&source_view=tag&source_id=' . (int) $this->tag->id . $itemIdParam); ?>">
                            <?php echo $this->escape($item->name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php echo $this->pagination->getListFooter(); ?>
        <?php endif; ?>
    </div>
</div>
