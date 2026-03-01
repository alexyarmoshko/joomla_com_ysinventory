<?php

/**
 * Yak Shaver Inventory — brands list template (site)
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

/** @var \YakShaver\Component\Ysinventory\Site\View\Brands\HtmlView $this */
?>
<div class="com-ysinventory-brands">
    <h2>
        <?php echo Text::_('COM_YSINVENTORY_BRANDS'); ?>
    </h2>

    <?php if (empty($this->items)): ?>
        <p class="alert alert-info">
            <?php echo Text::_('COM_YSINVENTORY_NO_BRANDS'); ?>
        </p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($this->items as $item): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=brand&id=' . (int) $item->id); ?>">
                        <?php echo $this->escape($item->name); ?>
                    </a>
                    <span class="badge bg-secondary rounded-pill">
                        <?php echo (int) $item->item_count; ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php echo $this->pagination->getListFooter(); ?>
    <?php endif; ?>
</div>