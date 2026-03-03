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

/** @var \YakShaver\Component\Ysinventory\Site\View\Lend\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$isNew = empty($this->item->id);
?>
<div class="com-ysinventory-lend-edit">
    <h2>
        <?php echo $isNew ? Text::_('COM_YSINVENTORY_LEND_NEW') : Text::_('COM_YSINVENTORY_LEND_EDIT'); ?>
    </h2>

    <form action="<?php echo Route::_('index.php?option=com_ysinventory&layout=edit&id=' . (int) $this->item->id); ?>"
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
            <a href="<?php echo Route::_('index.php?option=com_ysinventory&view=lends'); ?>"
                class="btn btn-secondary ms-2">
                <?php echo Text::_('JCANCEL'); ?>
            </a>
        </div>

        <input type="hidden" name="task" value="">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>