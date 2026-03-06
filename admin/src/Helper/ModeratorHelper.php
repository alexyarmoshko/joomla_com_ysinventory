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
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class ModeratorHelper
{
    /**
     * Check whether the given user belongs to a configured moderation group.
     *
     * Grants moderator status if the user has core.edit ACL on the component
     * or belongs to one of the configured lend moderation groups (component-level
     * or category-level).
     *
     * When $catId is provided, only that category's moderation groups are checked
     * (with component-level fallback). When $catId is null, all category-level
     * moderation groups are checked in addition to the component-level groups.
     *
     * @param   User      $user   The user to check.
     * @param   int|null  $catId  Optional category ID to scope the check.
     *
     * @return  bool
     */
    public static function isModerator(User $user, ?int $catId = null): bool
    {
        if ($user->guest) {
            return false;
        }

        if ($user->authorise('core.edit', 'com_ysinventory')) {
            return true;
        }

        $userGroups = $user->getAuthorisedGroups();

        // Component-level moderation groups.
        $componentGroups = (array) ComponentHelper::getParams('com_ysinventory')
            ->get('ysi_lend_moderation_groups', []);

        if (!empty($componentGroups) && !empty(array_intersect($userGroups, $componentGroups))) {
            return true;
        }

        // Category-level moderation groups.
        $catGroups = self::getCategoryModerationGroups($catId);

        if (!empty($catGroups) && !empty(array_intersect($userGroups, $catGroups))) {
            return true;
        }

        return false;
    }

    /**
     * Check whether the user is a global (component-level) moderator.
     *
     * Returns true only for core.edit ACL or component-level moderation groups.
     * Does NOT consider category-level groups.
     *
     * @param   User  $user  The user to check.
     *
     * @return  bool
     */
    public static function isGlobalModerator(User $user): bool
    {
        if ($user->guest) {
            return false;
        }

        if ($user->authorise('core.edit', 'com_ysinventory')) {
            return true;
        }

        $componentGroups = (array) ComponentHelper::getParams('com_ysinventory')
            ->get('ysi_lend_moderation_groups', []);

        if (!empty($componentGroups)) {
            $userGroups = $user->getAuthorisedGroups();

            if (!empty(array_intersect($userGroups, $componentGroups))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the category IDs the user can moderate through category-level groups.
     *
     * Does NOT include categories covered by global moderation — use
     * isGlobalModerator() to test that separately.
     *
     * @param   User  $user  The user to check.
     *
     * @return  int[]  Category IDs (may be empty).
     */
    public static function getModeratedCategoryIds(User $user): array
    {
        if ($user->guest) {
            return [];
        }

        $userGroups = $user->getAuthorisedGroups();

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('params')])
            ->from($db->quoteName('#__ysi_categories'))
            ->where($db->quoteName('level') . ' > 0');
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        $catIds = [];

        foreach ($rows as $row) {
            if (empty($row->params) || !\is_string($row->params)) {
                continue;
            }

            $catParams = new Registry($row->params);
            $catGroups = (array) $catParams->get('ysi_lend_moderation_groups', []);
            $catGroups = array_map('intval', $catGroups);
            $catGroups = array_filter($catGroups, static fn (int $id): bool => $id > 0);

            if (!empty($catGroups) && !empty(array_intersect($userGroups, $catGroups))) {
                $catIds[] = (int) $row->id;
            }
        }

        return $catIds;
    }

    /**
     * Load moderation group IDs from category params.
     *
     * When $catId is provided and > 0, loads groups for that category only.
     * When $catId is null, loads groups from all categories.
     *
     * @param   int|null  $catId  Optional category ID.
     *
     * @return  array  Integer group IDs.
     */
    private static function getCategoryModerationGroups(?int $catId): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__ysi_categories'));

        if ($catId !== null && $catId > 0) {
            $query->where($db->quoteName('id') . ' = :catId')
                ->bind(':catId', $catId, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        $rows = $db->loadColumn() ?: [];

        $groups = [];

        foreach ($rows as $paramsJson) {
            if (!\is_string($paramsJson) || trim($paramsJson) === '') {
                continue;
            }

            $catParams = new Registry($paramsJson);
            $catGroups = (array) $catParams->get('ysi_lend_moderation_groups', []);
            $groups = array_merge($groups, $catGroups);
        }

        $groups = array_map('intval', $groups);
        $groups = array_filter($groups, static fn (int $id): bool => $id > 0);

        return array_values(array_unique($groups));
    }
}
