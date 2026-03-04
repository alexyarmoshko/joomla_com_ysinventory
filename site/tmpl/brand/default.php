<?php

/**
 * Yak Shaver Inventory — single brand template (site)
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

/** @var \YakShaver\Component\Ysinventory\Site\View\Brand\HtmlView $this */

$itemId = Factory::getApplication()->getInput()->getInt('Itemid');
$itemIdParam = $itemId > 0 ? '&Itemid=' . $itemId : '';
?>
<div class="com-ysinventory-brand">
    <h2>
        <?php echo $this->escape($this->brand->name); ?>
    </h2>

    <?php if (!empty($this->brand->image)): ?>
        <div class="com-ysinventory-brand__image mb-3">
            <img src="<?php echo $this->escape($this->brand->image); ?>"
                alt="<?php echo $this->escape($this->brand->name); ?>" class="img-fluid">
        </div>
    <?php endif; ?>

    <?php if (!empty($this->brand->description)): ?>
        <div class="com-ysinventory-brand__description mb-4">
            <?php echo $this->brand->description; ?>
        </div>
    <?php endif; ?>

    <div class="com-ysinventory-brand__items">
        <h3>
            <?php echo Text::_('COM_YSINVENTORY_ITEMS_IN_BRAND'); ?>
        </h3>

        <?php if (empty($this->items)): ?>
            <p class="alert alert-info">
                <?php echo Text::_('COM_YSINVENTORY_NO_ITEMS_IN_BRAND'); ?>
            </p>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($this->items as $item): ?>
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
