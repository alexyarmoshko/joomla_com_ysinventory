<?php

/**
 * Yak Shaver Inventory — single category view (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\View\Category;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $category;
    protected $children;
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->category   = $model->getCategory();
        $this->children   = $model->getChildren();
        $this->items      = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state      = $model->getState();

        if ($this->category === false) {
            throw new GenericDataException(Text::_('COM_YSINVENTORY_ERROR_CATEGORY_NOT_FOUND'), 404);
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
        $app     = Factory::getApplication();
        $pathway = $app->getPathway();

        $pathway->addItem(
            Text::_('COM_YSINVENTORY_CATEGORIES'),
            $this->resolveMenuRoute('categories')
        );

        if ($this->category) {
            $ancestors = $this->getModel()->getAncestors();

            foreach ($ancestors as $ancestor) {
                $pathway->addItem(
                    $ancestor->title,
                    $this->resolveMenuRoute('category', (int) $ancestor->id)
                );
            }

            $pathway->addItem($this->category->title);
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
        $document = $this->getDocument();

        if ($this->category) {
            $document->setTitle($this->category->title);

            if (!empty($this->category->metadesc)) {
                $document->setDescription($this->category->metadesc);
            }

            if (!empty($this->category->metakey)) {
                $document->setMetaData('keywords', $this->category->metakey);
            }
        }
    }
}
