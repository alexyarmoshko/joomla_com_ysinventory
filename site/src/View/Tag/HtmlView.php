<?php

/**
 * Yak Shaver Inventory — single tag view (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\View\Tag;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $tag;
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->tag = $model->getTag();
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();

        if ($this->tag === false) {
            throw new GenericDataException(Text::_('COM_YSINVENTORY_ERROR_TAG_NOT_FOUND'), 404);
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
            Text::_('COM_YSINVENTORY_TAGS'),
            'index.php?option=com_ysinventory&view=tags'
        );

        if ($this->tag) {
            $pathway->addItem($this->tag->name);
        }
    }

    protected function prepareDocument()
    {
        if ($this->tag) {
            $this->getDocument()->setTitle($this->tag->name);
        }
    }
}
