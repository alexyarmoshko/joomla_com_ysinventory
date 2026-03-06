<?php

/**
 * Yak Shaver Inventory installer script.
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\InstallerAdapter;

class Com_YsinventoryInstallerScript
{
    /**
     * Remove obsolete frontend menu metadata files that Joomla will otherwise keep on update.
     *
     * @param   string            $type     Install type.
     * @param   InstallerAdapter  $adapter  Installer adapter.
     *
     * @return  bool
     */
    public function postflight($type, InstallerAdapter $adapter): bool
    {
        $root = rtrim(JPATH_SITE, '\\/');
        $obsoleteFiles = [
            $root . '/components/com_ysinventory/tmpl/brand/default.xml',
            $root . '/components/com_ysinventory/tmpl/category/default.xml',
            $root . '/components/com_ysinventory/tmpl/tag/default.xml',
            $root . '/components/com_ysinventory/tmpl/item/default.xml',
        ];

        foreach ($obsoleteFiles as $file) {
            if (is_file($file)) {
                File::delete($file);
            }
        }

        return true;
    }
}
