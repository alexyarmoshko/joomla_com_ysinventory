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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $item;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->item = $model->getItem();

        if ($this->item === false) {
            throw new GenericDataException(Text::_('COM_YSINVENTORY_ERROR_ITEM_NOT_FOUND'), 404);
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
            Text::_('COM_YSINVENTORY_ITEMS'),
            'index.php?option=com_ysinventory&view=items'
        );

        if ($this->item) {
            $pathway->addItem($this->item->name);
        }
    }

    protected function prepareDocument()
    {
        if ($this->item) {
            $this->getDocument()->setTitle($this->item->name);
        }
    }
}
