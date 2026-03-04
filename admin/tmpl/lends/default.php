<?php

/**
 * Yak Shaver Inventory — lends list template (admin)
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

/** @var \YakShaver\Component\Ysinventory\Administrator\View\Lends\HtmlView $this */

require_once JPATH_SITE . '/components/com_ysinventory/tmpl/lend_status_helper.php';

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user = $this->getCurrentUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$statusLabels = ysinventoryGetLendStatusLabels();
?>
<form action="<?php echo Route::_('index.php?option=com_ysinventory&view=lends'); ?>" method="post" name="adminForm"
    id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php if (empty($this->items)): ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden">
                            <?php echo Text::_('INFO'); ?>
                        </span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else: ?>
                    <table class="table" id="lendList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_YSINVENTORY_LENDS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy">
                                <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
                            </span>,
                            <span id="filteredBy">
                                <?php echo Text::_('JGLOBAL_FILTERED_BY'); ?>
                            </span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.ysi_status', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_ITEM_LABEL'); ?>
                                </th>
                                <th scope="col" class="w-15 d-none d-md-table-cell">
                                    <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_USER_LABEL'); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_YSINVENTORY_FIELD_LEND_FROM_LABEL', 'a.ysi_from', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo Text::_('COM_YSINVENTORY_FIELD_LEND_TO_LABEL'); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_FIELD_CREATED_LABEL', 'a.created', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-5 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item):
                                $canEdit = $user->authorise('core.edit', 'com_ysinventory');
                                $statusInfo = $statusLabels[(int) $item->ysi_status] ?? ['JUNKNOWN', 'secondary'];
                                ?>
                                <tr class="row<?php echo $i % 2; ?>">
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->item_name ?? $item->id); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?php echo $statusInfo[1]; ?>">
                                            <?php echo Text::_($statusInfo[0]); ?>
                                        </span>
                                    </td>
                                    <th scope="row" class="has-context">
                                        <?php if ($canEdit): ?>
                                            <a href="<?php echo Route::_('index.php?option=com_ysinventory&task=lend.edit&id=' . (int) $item->id); ?>"
                                                title="<?php echo Text::_('JACTION_EDIT'); ?>">
                                                <?php echo $this->escape($item->item_name ?? ''); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo $this->escape($item->item_name ?? ''); ?>
                                        <?php endif; ?>
                                    </th>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo $this->escape($item->user_name ?? ''); ?>
                                    </td>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo HTMLHelper::_('date', $item->ysi_from, Text::_('DATE_FORMAT_LC4')); ?>
                                    </td>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo HTMLHelper::_('date', $item->ysi_to, Text::_('DATE_FORMAT_LC4')); ?>
                                    </td>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo $item->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
