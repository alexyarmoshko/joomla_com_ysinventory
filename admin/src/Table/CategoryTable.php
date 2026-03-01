<?php

/**
 * Yak Shaver Inventory — category table class (nested set)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Nested;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

class CategoryTable extends Nested implements CurrentUserInterface
{
    use CurrentUserTrait;

    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_ysinventory.category';

        parent::__construct('#__ysi_categories', 'id', $db, $dispatcher);

        $this->access = 1;
    }

    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return 'com_ysinventory.category.' . (int) $this->$k;
    }

    protected function _getAssetTitle()
    {
        return $this->title;
    }

    protected function _getAssetParentId($table = null, $id = null)
    {
        if ($this->parent_id && $this->parent_id != $this->id) {
            $parentTable = new self($this->getDatabase(), $this->getDispatcher());

            if ($parentTable->load($this->parent_id) && $parentTable->asset_id) {
                return $parentTable->asset_id;
            }
        }

        // Look up the component asset directly via query.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' = ' . $db->quote('com_ysinventory'));
        $db->setQuery($query);

        $assetId = (int) $db->loadResult();

        if ($assetId > 0) {
            return $assetId;
        }

        return parent::_getAssetParentId($table, $id);
    }

    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (trim($this->title) === '') {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_CATEGORY_PROVIDE_VALID_TITLE'));

            return false;
        }

        $this->generateAlias();

        if (trim($this->alias) === '') {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_CATEGORY_PROVIDE_VALID_TITLE'));

            return false;
        }

        if (empty($this->parent_id)) {
            $this->parent_id = 1;
        }

        if ((int) $this->access === 0) {
            $this->access = 1;
        }

        if (empty($this->params)) {
            $this->params = '{}';
        }

        if (empty($this->language)) {
            $this->language = '*';
        }

        return true;
    }

    public function store($updateNulls = true)
    {
        $date   = Factory::getDate()->toSql();
        $userId = $this->getCurrentUser()->id;

        $isNew = empty($this->id);

        if ($isNew) {
            if (empty($this->created_time)) {
                $this->created_time = $date;
            }

            if (empty($this->created_user_id)) {
                $this->created_user_id = $userId;
            }
        }

        $this->modified_time    = $date;
        $this->modified_user_id = $userId;

        // Rebuild path from alias hierarchy.
        $this->buildPath();

        // Verify alias uniqueness within same parent.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($this->_tbl))
            ->where($db->quoteName('alias') . ' = :alias')
            ->where($db->quoteName('parent_id') . ' = :parentId')
            ->bind(':alias', $this->alias)
            ->bind(':parentId', $this->parent_id, \Joomla\Database\ParameterType::INTEGER);

        if (!$isNew) {
            $query->where($db->quoteName('id') . ' != :id')
                ->bind(':id', $this->id, \Joomla\Database\ParameterType::INTEGER);
        }

        $db->setQuery($query);

        if ((int) $db->loadResult() > 0) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_CATEGORY_UNIQUE_ALIAS'));

            return false;
        }

        $result = parent::store($updateNulls);

        // After a successful store, rebuild descendant paths so they stay consistent
        // when a category is renamed or moved to a different parent.
        if ($result && !$isNew) {
            $this->rebuildDescendantPaths();
        }

        return $result;
    }

    public function generateAlias()
    {
        if (empty($this->alias)) {
            $this->alias = $this->title;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language ?? '');

        if (trim(str_replace('-', '', $this->alias)) === '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return $this->alias;
    }

    public function buildPath()
    {
        if ((int) $this->parent_id <= 1) {
            $this->path = $this->alias;

            return;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('path'))
            ->from($db->quoteName($this->_tbl))
            ->where($db->quoteName('id') . ' = :parentId')
            ->bind(':parentId', $this->parent_id, \Joomla\Database\ParameterType::INTEGER);
        $db->setQuery($query);

        $parentPath = (string) $db->loadResult();

        $this->path = $parentPath !== '' ? $parentPath . '/' . $this->alias : $this->alias;
    }

    protected function rebuildDescendantPaths()
    {
        $db = $this->getDatabase();

        // Find all descendants using nested-set (lft between this node's lft and rgt).
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'alias', 'parent_id']))
            ->from($db->quoteName($this->_tbl))
            ->where($db->quoteName('lft') . ' > :lft')
            ->where($db->quoteName('rgt') . ' < :rgt')
            ->bind(':lft', $this->lft, \Joomla\Database\ParameterType::INTEGER)
            ->bind(':rgt', $this->rgt, \Joomla\Database\ParameterType::INTEGER)
            ->order($db->quoteName('lft') . ' ASC');
        $db->setQuery($query);
        $descendants = $db->loadObjectList('id');

        if (empty($descendants)) {
            return;
        }

        // Build a path lookup starting from this node.
        $paths       = [];
        $paths[(int) $this->id] = $this->path;

        foreach ($descendants as $desc) {
            $parentPath = $paths[(int) $desc->parent_id] ?? '';
            $newPath    = $parentPath !== '' ? $parentPath . '/' . $desc->alias : $desc->alias;
            $paths[(int) $desc->id] = $newPath;

            $update = $db->getQuery(true)
                ->update($db->quoteName($this->_tbl))
                ->set($db->quoteName('path') . ' = :path')
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':path', $newPath)
                ->bind(':id', $desc->id, \Joomla\Database\ParameterType::INTEGER);
            $db->setQuery($update);
            $db->execute();
        }
    }

    public function delete($pk = null, $children = true)
    {
        $pk = $pk ?: $this->id;

        // Prevent deletion if items reference this category (placeholder for Phase 6).
        // Items table does not exist yet; this guard will be activated when #__ysi_items is created.
        $db = $this->getDatabase();

        try {
            $columns = $db->getTableColumns('#__ysi_items', false);

            if (isset($columns['catid'])) {
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__ysi_items'))
                    ->where($db->quoteName('catid') . ' = :catid')
                    ->bind(':catid', $pk, \Joomla\Database\ParameterType::INTEGER);
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    $this->setError(Text::_('COM_YSINVENTORY_ERROR_CATEGORY_HAS_ITEMS'));

                    return false;
                }
            }
        } catch (\RuntimeException $e) {
            // Items table does not exist yet — safe to delete.
        }

        return parent::delete($pk, $children);
    }

    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
