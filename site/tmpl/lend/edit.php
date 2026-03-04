<?php

/**
 * Yak Shaver Inventory — lend edit template (site)
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
use Joomla\CMS\Factory;

/** @var \YakShaver\Component\Ysinventory\Site\View\Lend\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
$wa->addInlineStyle(<<<'CSS'
.com-ysinventory-lend-edit .row,
.com-ysinventory-lend-edit [class*="col-"] {
    overflow: visible;
}

.com-ysinventory-lend-edit joomla-field-fancy-select {
    display: block;
}

.com-ysinventory-lend-edit joomla-field-fancy-select .choices {
    position: relative;
    margin-bottom: 0;
}

.com-ysinventory-lend-edit joomla-field-fancy-select .choices__list--dropdown,
.com-ysinventory-lend-edit joomla-field-fancy-select .choices__list[aria-expanded] {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: 0 !important;
    margin-top: 0.35rem;
    max-height: 18rem;
    overflow-y: auto;
    background-color: #f3f4f6;
    opacity: 1;
    z-index: 1090;
}

.com-ysinventory-lend-edit joomla-field-fancy-select .choices__list--dropdown .choices__item,
.com-ysinventory-lend-edit joomla-field-fancy-select .choices__list[aria-expanded] .choices__item {
    background-color: #f3f4f6;
}

.com-ysinventory-lend-edit joomla-field-fancy-select .choices__list--dropdown .choices__item--selectable[aria-selected='true'],
.com-ysinventory-lend-edit joomla-field-fancy-select .choices__list[aria-expanded] .choices__item--selectable[aria-selected='true'] {
    background-color: #e5e7eb;
}

.com-ysinventory-lend-edit .calendar-container {
    --btn-primary-bg: #0d6efd;
    --btn-primary-color: #ffffff;
    --calendar-bg: #ffffff;
    --calendar-disabled-bg: #f3f4f6;
    --calendar-disabled-color: #9ca3af;
}
CSS
);

$isNew = empty($this->item->id);
$itemId = Factory::getApplication()->getInput()->getInt('Itemid');
$itemIdParam = $itemId > 0 ? '&Itemid=' . $itemId : '';
?>
<div class="com-ysinventory-lend-edit">
    <h2>
        <?php echo $isNew ? Text::_('COM_YSINVENTORY_LEND_NEW') : Text::_('COM_YSINVENTORY_LEND_EDIT'); ?>
    </h2>

    <form action="<?php echo Route::_('index.php?option=com_ysinventory&view=lend&layout=edit&id=' . (int) $this->item->id . $itemIdParam); ?>"
        method="post" name="adminForm" id="adminForm"
        aria-label="<?php echo Text::_($isNew ? 'COM_YSINVENTORY_LEND_NEW' : 'COM_YSINVENTORY_LEND_EDIT', true); ?>"
        class="form-validate">

        <div class="row">
            <div class="col-lg-8">
                <?php echo $this->form->renderField('ysi_item_id'); ?>
                <?php echo $this->form->renderField('ysi_user_id'); ?>
                <?php echo $this->form->renderField('ysi_note'); ?>
            </div>
            <div class="col-lg-4">
                <?php echo $this->form->renderField('ysi_status'); ?>
                <?php echo $this->form->renderField('ysi_from'); ?>
                <?php echo $this->form->renderField('ysi_to'); ?>
            </div>
        </div>

        <div class="mt-3">
            <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('lend.save')">
                <?php echo Text::_('JSAVE'); ?>
            </button>
            <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=lends' . $itemIdParam); ?>"
                class="btn btn-secondary ms-2">
                <?php echo Text::_('JCANCEL'); ?>
            </a>
        </div>

        <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
        <?php if (!$isNew) : ?>
            <input type="hidden" name="jform[ysi_item_id]" value="<?php echo (int) $this->item->ysi_item_id; ?>">
            <input type="hidden" name="jform[ysi_user_id]" value="<?php echo (int) $this->item->ysi_user_id; ?>">
        <?php endif; ?>
        <input type="hidden" name="task" value="">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
