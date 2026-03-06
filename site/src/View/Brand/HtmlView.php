<?php

/**
 * Yak Shaver Inventory — single brand view (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\View\Brand;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $brand;
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->brand = $model->getBrand();
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();

        if ($this->brand === false) {
            throw new GenericDataException(Text::_('COM_YSINVENTORY_ERROR_BRAND_NOT_FOUND'), 404);
        }

        if (!\is_array($this->items)) {
            $this->items = [];
        }

        $this->prepareBreadcrumbs();
        $this->prepareDocument();

        parent::display($tpl);
    }

    protected function prepareBreadcrumbs()
    {
        $app = Factory::getApplication();
        $pathway = $app->getPathway();

        $pathway->addItem(
            Text::_('COM_YSINVENTORY_BRANDS'),
            $this->resolveMenuRoute('brands')
        );

        if ($this->brand) {
            $pathway->addItem($this->brand->name);
        }
    }

    protected function resolveMenuRoute(string $view, int $id = 0): string
    {
        $app = Factory::getApplication();
        $menu = $app->getMenu();

        if ($menu instanceof AbstractMenu) {
            $active = $menu->getActive();

            if ($this->menuItemMatches($active, $view, $id)) {
                return 'index.php?Itemid=' . (int) $active->id;
            }

            foreach ($menu->getMenu() as $menuItem) {
                if ($this->menuItemMatches($menuItem, $view, $id)) {
                    return 'index.php?Itemid=' . (int) $menuItem->id;
                }
            }
        }

        $route = 'index.php?option=com_ysinventory&view=' . $view;

        if ($id > 0) {
            $route .= '&id=' . $id;
        }

        return $route;
    }

    protected function menuItemMatches($menuItem, string $view, int $id = 0): bool
    {
        if (!$menuItem) {
            return false;
        }

        $query = $menuItem->query ?? [];

        if (($query['option'] ?? '') !== 'com_ysinventory' || ($query['view'] ?? '') !== $view) {
            return false;
        }

        if ($id > 0) {
            return (int) ($query['id'] ?? 0) === $id;
        }

        return true;
    }

    protected function prepareDocument()
    {
        if ($this->brand) {
            $this->getDocument()->setTitle($this->brand->name);
        }
    }
}
