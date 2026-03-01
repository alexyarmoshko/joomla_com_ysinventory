<?php

/**
 * Yak Shaver Inventory — inventory edit template (admin)
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

/** @var \YakShaver\Component\Ysinventory\Administrator\View\Inventory\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$this->getDocument()->addScriptDeclaration(
    <<<'JS'
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('inventory-form');

    if (!form) {
        return;
    }

    const getFieldInputs = (fieldName) => {
        const inputs = Array.from(form.querySelectorAll(`input[name="jform[${fieldName}]"]`));
        const hidden = inputs.find((input) => input.type === 'hidden') || null;
        const title = inputs.find((input) => input.type !== 'hidden') || null;

        return { hidden, title, inputs };
    };

    const setInputValue = (input, value) => {
        if (!input) {
            return;
        }

        input.value = value;
        input.setAttribute('value', value);
    };

    const getInputValue = (input) => {
        if (!input) {
            return '';
        }

        const value = `${input.value ?? ''}`.trim();

        if (value !== '') {
            return value;
        }

        return `${input.getAttribute('value') ?? ''}`.trim();
    };

    const hasOwnerValue = (input) => {
        const value = getInputValue(input);
        return value !== '' && value !== '0';
    };

    const userInputs = getFieldInputs('ysi_contact_user_id');
    const userInput = userInputs.hidden;
    const userNameInput = userInputs.title;
    const userField = form.querySelector('joomla-field-user');
    const contactInputs = getFieldInputs('ysi_contact_contact_id');
    const contactInput = contactInputs.hidden;
    const contactNameInput = contactInputs.title;

    if (!userInput || !contactInput) {
        return;
    }

    let ownerLastSelectedInput = form.querySelector('input[name="jform[ysi_owner_last_selected]"]');

    if (!ownerLastSelectedInput) {
        ownerLastSelectedInput = document.createElement('input');
        ownerLastSelectedInput.type = 'hidden';
        ownerLastSelectedInput.name = 'jform[ysi_owner_last_selected]';
        ownerLastSelectedInput.value = '';
        form.appendChild(ownerLastSelectedInput);
    }

    // Prevent duplicate-name title inputs from being posted instead of numeric IDs.
    userInputs.inputs
        .filter((input) => input.type !== 'hidden')
        .forEach((input) => input.removeAttribute('name'));
    contactInputs.inputs
        .filter((input) => input.type !== 'hidden')
        .forEach((input) => input.removeAttribute('name'));

    let syncing = false;
    let lastOwnerChanged = '';

    const clearUser = () => {
        if (userField && typeof userField.setValue === 'function') {
            userField.setValue('', '');
        }

        const changed = hasOwnerValue(userInput);
        setInputValue(userInput, '');
        setInputValue(userNameInput, '');

        if (changed) {
            userInput.dispatchEvent(new CustomEvent('change', { bubbles: true, cancelable: true }));
        }
    };

    const clearContact = () => {
        const changed = hasOwnerValue(contactInput);
        setInputValue(contactInput, '');
        setInputValue(contactNameInput, '');

        if (changed) {
            contactInput.dispatchEvent(new CustomEvent('change', { bubbles: true, cancelable: true }));
        }
    };

    form.addEventListener('change', (event) => {
        if (syncing) {
            return;
        }

        const target = event.target;
        const targetId = target && target.id ? target.id : '';
        const targetName = target && target.name ? target.name : '';
        const userChanged = target === userInput
            || target === userField
            || targetId.includes('ysi_contact_user_id')
            || targetName.includes('[ysi_contact_user_id]');
        const contactChanged = target === contactInput
            || targetId.includes('ysi_contact_contact_id')
            || targetName.includes('[ysi_contact_contact_id]');

        if (userChanged && hasOwnerValue(userInput)) {
            lastOwnerChanged = 'user';
            ownerLastSelectedInput.value = 'user';
            syncing = true;
            clearContact();
            syncing = false;
        } else if (contactChanged && hasOwnerValue(contactInput)) {
            lastOwnerChanged = 'contact';
            ownerLastSelectedInput.value = 'contact';
            syncing = true;
            clearUser();
            syncing = false;
        }
    }, true);

    form.addEventListener('submit', () => {
        const userValue = getInputValue(userInput);
        const contactValue = getInputValue(contactInput);

        // Normalize runtime values before submit because Joomla user field uses setAttribute().
        setInputValue(userInput, userValue);
        setInputValue(contactInput, contactValue);

        if (!hasOwnerValue(userInput) || !hasOwnerValue(contactInput)) {
            return;
        }

        if (lastOwnerChanged === 'contact') {
            clearUser();
            ownerLastSelectedInput.value = 'contact';
        } else {
            clearContact();
            ownerLastSelectedInput.value = 'user';
        }
    }, true);
});
JS
);

?>
<form action="<?php echo Route::_('index.php?option=com_ysinventory&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="inventory-form"
    aria-label="<?php echo Text::_('COM_YSINVENTORY_FORM_TITLE_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
    class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_YSINVENTORY_INVENTORY_NEW') : Text::_('COM_YSINVENTORY_INVENTORY_EDIT')); ?>
        <div class="row">
            <div class="col-lg-9">
                <fieldset id="fieldset-owner" class="options-form">
                    <legend><?php echo Text::_('COM_YSINVENTORY_FIELDSET_OWNER_LABEL'); ?></legend>
                    <?php echo $this->form->renderField('ysi_contact_user_id'); ?>
                    <?php echo $this->form->renderField('ysi_contact_contact_id'); ?>
                </fieldset>
                <?php echo $this->form->renderField('description'); ?>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('ordering'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
        <div class="row">
            <div class="col-md-6">
                <fieldset id="fieldset-publishingdata" class="options-form">
                    <legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
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
