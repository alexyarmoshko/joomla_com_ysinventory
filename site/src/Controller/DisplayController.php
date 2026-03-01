<?php

/**
 * Yak Shaver Inventory — site display controller
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Site
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    /**
     * The default view for the site side.
     *
     * @var string
     */
    protected $default_view = 'ysinventory';

    public function display($cachable = true, $urlparams = [])
    {
        $urlparams['id']     = 'INT';
        $urlparams['catid']  = 'INT';
        $urlparams['view']   = 'CMD';
        $urlparams['layout'] = 'CMD';

        return parent::display($cachable, $urlparams);
    }
}
