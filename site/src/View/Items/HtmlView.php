<?php

/**
 * Yak Shaver Inventory — items list view (site)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\View\Items;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterOptions;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->filterOptions = $model->getFilterOptions();

        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
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

        $pathway->addItem(Text::_('COM_YSINVENTORY_ITEMS'));
    }

    protected function prepareDocument()
    {
        $document = $this->getDocument();
        $document->setTitle(Text::_('COM_YSINVENTORY_ITEMS'));
        $document->getWebAssetManager()->useStyle('fontawesome');
    }
}
