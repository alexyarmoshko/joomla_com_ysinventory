<?php

/**
 * Yak Shaver Inventory — inventories empty state template (admin)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \YakShaver\Component\Ysinventory\Administrator\View\Inventories\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_YSINVENTORY_INVENTORIES',
    'formURL'    => 'index.php?option=com_ysinventory&view=inventories',
    'icon'       => 'icon-archive',
];

$user = $this->getCurrentUser();

if ($user->authorise('core.create', 'com_ysinventory')) {
    $displayData['createURL'] = 'index.php?option=com_ysinventory&task=inventory.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
