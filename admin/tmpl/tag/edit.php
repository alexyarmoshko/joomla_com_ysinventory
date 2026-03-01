<?php

/**
 * Yak Shaver Inventory — tag edit template (admin)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \YakShaver\Component\Ysinventory\Administrator\View\Tag\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
?>
<form action="<?php echo Route::_('index.php?option=com_ysinventory&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="tag-form"
    aria-label="<?php echo Text::_('COM_YSINVENTORY_FORM_TITLE_TAG_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
    class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_YSINVENTORY_TAG_NEW') : Text::_('COM_YSINVENTORY_TAG_EDIT')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('description'); ?>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderField('ysi_tag_group_id'); ?>
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('ordering'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
        <div class="row">
            <div class="col-md-6">
                <fieldset id="fieldset-publishingdata" class="options-form">
                    <legend>
                        <?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?>
                    </legend>
                    <div>
                        <?php echo $this->form->renderField('created'); ?>
                        <?php echo $this->form->renderField('created_by'); ?>
                        <?php echo $this->form->renderField('modified'); ?>
                        <?php echo $this->form->renderField('modified_by'); ?>
                        <?php echo $this->form->renderField('id'); ?>
                    </div>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>