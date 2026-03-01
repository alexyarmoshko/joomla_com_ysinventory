<?php

/**
 * Yak Shaver Inventory — single location edit view (admin)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\View\Location;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $state;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->form = $model->getForm();
        $this->item = $model->getItem();
        $this->state = $model->getState();

        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $user = $this->getCurrentUser();
        $userId = $user->id;
        $isNew = ($this->item->id == 0);
        $checkedOut = !(\is_null($this->item->checked_out) || $this->item->checked_out == $userId);
        $toolbar = $this->getDocument()->getToolbar();
        $canDo = ContentHelper::getActions('com_ysinventory');

        ToolbarHelper::title(
            $isNew ? Text::_('COM_YSINVENTORY_LOCATION_NEW') : Text::_('COM_YSINVENTORY_LOCATION_EDIT'),
            'location'
        );

        if ($isNew) {
            if ($canDo->get('core.create')) {
                $toolbar->apply('location.apply');

                $saveGroup = $toolbar->dropdownButton('save-group');
                $saveGroup->configure(
                    function (Toolbar $childBar) {
                        $childBar->save('location.save');
                        $childBar->save2new('location.save2new');
                    }
                );
            }

            $toolbar->cancel('location.cancel', 'JTOOLBAR_CANCEL');
        } else {
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

            if (!$checkedOut && $itemEditable) {
                $toolbar->apply('location.apply');
            }

            $saveGroup = $toolbar->dropdownButton('save-group');
            $saveGroup->configure(
                function (Toolbar $childBar) use ($checkedOut, $itemEditable, $canDo) {
                    if (!$checkedOut && $itemEditable) {
                        $childBar->save('location.save');

                        if ($canDo->get('core.create')) {
                            $childBar->save2new('location.save2new');
                        }
                    }

                    if ($canDo->get('core.create')) {
                        $childBar->save2copy('location.save2copy');
                    }
                }
            );

            $toolbar->cancel('location.cancel');
        }
    }
}
