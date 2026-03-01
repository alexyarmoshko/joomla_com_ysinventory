<?php

/**
 * Yak Shaver Inventory — locations list view (admin)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\View\Locations;

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    public $filterForm;
    public $activeFilters;
    private $isEmptyState = false;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if (!\is_array($this->items)) {
            $this->items = [];
        }

        if (!\count($this->items) && $this->isEmptyState = $model->getIsEmptyState()) {
            $this->setLayout('emptystate');
        }

        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        }

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $canDo = ContentHelper::getActions('com_ysinventory');
        $user = $this->getCurrentUser();
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_YSINVENTORY_LOCATIONS'), 'location');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('location.add');
        }

        if (!$this->isEmptyState && ($canDo->get('core.edit.state') || $canDo->get('core.delete'))) {
            /** @var DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($canDo->get('core.edit.state')) {
                $childBar->publish('locations.publish')->listCheck(true);
                $childBar->unpublish('locations.unpublish')->listCheck(true);

                if ($user->authorise('core.admin')) {
                    $childBar->checkin('locations.checkin');
                }

                if ($this->state->get('filter.published') != -2) {
                    $childBar->trash('locations.trash')->listCheck(true);
                }
            }

            if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
                $toolbar->delete('locations.delete', 'JTOOLBAR_DELETE_FROM_TRASH')
                    ->message('JGLOBAL_CONFIRM_DELETE')
                    ->listCheck(true);
            }
        }

        if ($user->authorise('core.admin', 'com_ysinventory') || $user->authorise('core.options', 'com_ysinventory')) {
            $toolbar->preferences('com_ysinventory');
        }
    }
}
