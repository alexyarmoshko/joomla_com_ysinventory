<?php

/**
 * Yak Shaver Inventory — category edit template (admin)
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

/** @var \YakShaver\Component\Ysinventory\Administrator\View\Category\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
?>
<form action="<?php echo Route::_('index.php?option=com_ysinventory&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="category-form"
    aria-label="<?php echo Text::_('COM_YSINVENTORY_CATEGORY_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
    class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_YSINVENTORY_CATEGORY_FIELDSET_DETAILS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('description'); ?>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderField('parent_id'); ?>
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('language'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'lending', Text::_('COM_YSINVENTORY_CATEGORY_FIELDSET_LENDING')); ?>
        <div class="row">
            <div class="col-lg-9">
                <fieldset id="fieldset-lending" class="options-form">
                    <legend><?php echo Text::_('COM_YSINVENTORY_CATEGORY_FIELDSET_LENDING'); ?></legend>
                    <div>
                        <?php foreach ($this->form->getFieldset('basic') as $field) : ?>
                            <?php echo $field->renderField(); ?>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
        <div class="row">
            <div class="col-md-6">
                <fieldset id="fieldset-publishingdata" class="options-form">
                    <legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
                    <div>
                        <?php echo $this->form->renderField('created_time'); ?>
                        <?php echo $this->form->renderField('created_user_id'); ?>
                        <?php echo $this->form->renderField('modified_time'); ?>
                        <?php echo $this->form->renderField('modified_user_id'); ?>
                        <?php echo $this->form->renderField('id'); ?>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-6">
                <fieldset id="fieldset-metadata" class="options-form">
                    <legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
                    <div>
                        <?php echo $this->form->renderField('metadesc'); ?>
                        <?php echo $this->form->renderField('metakey'); ?>
                    </div>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if ($this->form->getFieldset('rules')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JCONFIG_PERMISSIONS_LABEL')); ?>
        <div class="row">
            <div class="col-12">
                <fieldset id="fieldset-rules" class="options-form">
                    <legend><?php echo Text::_('JCONFIG_PERMISSIONS_LABEL'); ?></legend>
                    <div>
                        <?php echo $this->form->getInput('rules'); ?>
                    </div>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
