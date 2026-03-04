<?php

/**
 * Yak Shaver Inventory — single item view (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\View\Item;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;

class HtmlView extends BaseHtmlView
{
    protected $item;
    public $canRequestLend = false;
    public $showBorrowings = false;
    public $borrowings = [];
    public $borrowingsPagination = null;
    public $activeTab = 'details';

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->item = $model->getItem();

        if ($this->item === false) {
            throw new GenericDataException(Text::_('COM_YSINVENTORY_ERROR_ITEM_NOT_FOUND'), 404);
        }

        $user            = Factory::getApplication()->getIdentity();
        $componentParams = ComponentHelper::getParams('com_ysinventory');

        // Load category-level params with component fallback.
        $catParams     = $model->getCategoryParams((int) ($this->item->catid ?? 0));
        $requestGroups = (array) $catParams->get('ysi_lend_request_groups', []);

        if (empty($requestGroups)) {
            $requestGroups = (array) $componentParams->get('ysi_lend_request_groups', []);
        }

        $moderationGroups = (array) $catParams->get('ysi_lend_moderation_groups', []);

        if (empty($moderationGroups)) {
            $moderationGroups = (array) $componentParams->get('ysi_lend_moderation_groups', []);
        }

        // Determine whether the current user can submit a lend request.
        if (!$user->guest && $this->item) {
            if (!empty($requestGroups)) {
                $userGroups = $user->getAuthorisedGroups();

                if (!empty(array_intersect($userGroups, $requestGroups)) && ((int) $this->item->ysi_quantity > 0)) {
                    $this->canRequestLend = true;
                }
            }
        }

        // Borrowings tab: controlled by show_borrowings setting + moderation group membership.
        $showBorrowingsSetting = $catParams->get('ysi_lend_show_borrowings', '');

        if ($showBorrowingsSetting === '' || $showBorrowingsSetting === null) {
            $showBorrowingsSetting = $componentParams->get('ysi_lend_show_borrowings', '1');
        }

        if ((int) $showBorrowingsSetting === 1 && !$user->guest && $this->item && !empty($moderationGroups)) {
            $userGroups = $userGroups ?? $user->getAuthorisedGroups();

            if (!empty(array_intersect($userGroups, $moderationGroups))) {
                $this->showBorrowings = true;

                $app = Factory::getApplication();
                $defaultLimit = (int) $componentParams->get('list_limit', (int) $app->get('list_limit', 20));
                $limit = $app->getUserStateFromRequest('com_ysinventory.item.borrowings.limit', 'borrowings_limit', $defaultLimit, 'uint');
                $limitstart = $app->getUserStateFromRequest('com_ysinventory.item.borrowings.limitstart', 'borrowings_limitstart', 0, 'uint');
                $total = $model->getItemBorrowingsTotal((int) $this->item->id);

                $this->borrowingsPagination = new Pagination($total, $limitstart, $limit, 'borrowings_');
                $this->borrowingsPagination->setAdditionalUrlParam('active_tab', 'borrowings');
                $this->borrowings = $model->getItemBorrowings(
                    (int) $this->item->id,
                    (int) $this->borrowingsPagination->limitstart,
                    (int) $this->borrowingsPagination->limit
                );
            }
        }

        $requestedTab = Factory::getApplication()->getInput()->getCmd('active_tab', 'details');
        $allowedTabs = ['details'];

        if ($this->canRequestLend) {
            $allowedTabs[] = 'loan';
        }

        if ($this->showBorrowings) {
            $allowedTabs[] = 'borrowings';
        }

        if (\in_array($requestedTab, $allowedTabs, true)) {
            $this->activeTab = $requestedTab;
        }

        $this->prepareBreadcrumbs();
        $this->prepareDocument();

        parent::display($tpl);
    }

    protected function prepareBreadcrumbs()
    {
        $app     = Factory::getApplication();
        $pathway = $app->getPathway();
        $menu    = $app->getMenu();
        $active  = $menu ? $menu->getActive() : null;

        $activeOption = $active->query['option'] ?? '';
        $activeView   = $active->query['view'] ?? '';
        $activeId     = isset($active->query['id']) ? (int) $active->query['id'] : 0;
        $currentId    = (int) ($this->item->id ?? 0);

        $isItemsMenuContext = $activeOption === 'com_ysinventory' && $activeView === 'items';
        $isCurrentItemMenuContext = $activeOption === 'com_ysinventory'
            && $activeView === 'item'
            && $activeId === $currentId;

        if (!$isItemsMenuContext && !$isCurrentItemMenuContext) {
            $pathway->addItem(
                Text::_('COM_YSINVENTORY_ITEMS'),
                'index.php?option=com_ysinventory&view=items'
            );
        }

        if ($this->item) {
            $activeTitle = $active ? trim((string) $active->title) : '';
            $itemName    = trim((string) $this->item->name);

            if (!$isCurrentItemMenuContext || strcasecmp($activeTitle, $itemName) !== 0) {
                $pathway->addItem($this->item->name);
            }
        }
    }

    protected function prepareDocument()
    {
        if ($this->item) {
            $this->getDocument()->setTitle($this->item->name);
        }
    }
}
