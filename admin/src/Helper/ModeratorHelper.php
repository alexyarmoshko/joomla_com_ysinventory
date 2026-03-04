<?php

/**
 * Yak Shaver Inventory — moderation authorization helper
 *
 * Centralizes the check for whether a user belongs to a configured
 * lend moderation group (component-level).
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\User;

class ModeratorHelper
{
    /**
     * Check whether the given user belongs to a configured moderation group.
     *
     * Grants moderator status if the user has core.edit ACL on the component
     * or belongs to one of the configured lend moderation groups.
     *
     * @param   User  $user  The user to check.
     *
     * @return  bool
     */
    public static function isModerator(User $user): bool
    {
        if ($user->guest) {
            return false;
        }

        if ($user->authorise('core.edit', 'com_ysinventory')) {
            return true;
        }

        $moderationGroups = (array) ComponentHelper::getParams('com_ysinventory')
            ->get('ysi_lend_moderation_groups', []);

        if (!empty($moderationGroups)) {
            $userGroups = $user->getAuthorisedGroups();

            if (!empty(array_intersect($userGroups, $moderationGroups))) {
                return true;
            }
        }

        return false;
    }
}
