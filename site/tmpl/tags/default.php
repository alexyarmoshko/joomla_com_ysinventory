<?php

/**
 * Yak Shaver Inventory — tags grouped list template (site)
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

/** @var \YakShaver\Component\Ysinventory\Site\View\Tags\HtmlView $this */
?>
<div class="com-ysinventory-tags">
    <h2>
        <?php echo Text::_('COM_YSINVENTORY_TAGS'); ?>
    </h2>

    <?php if (empty($this->groupedTags)): ?>
        <p class="alert alert-info">
            <?php echo Text::_('COM_YSINVENTORY_NO_TAGS'); ?>
        </p>
    <?php else: ?>
        <?php foreach ($this->groupedTags as $group): ?>
            <div class="com-ysinventory-tags__group mb-4">
                <h3>
                    <?php echo $this->escape($group['group']->name); ?>
                </h3>
                <ul class="list-group">
                    <?php foreach ($group['tags'] as $tag): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=tag&id=' . (int) $tag->id); ?>">
                                <?php echo $this->escape($tag->name); ?>
                            </a>
                            <span class="badge bg-secondary rounded-pill">
                                <?php echo (int) $tag->item_count; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <?php echo $this->pagination->getListFooter(); ?>
    <?php endif; ?>
</div>