<?php

/**
 * Yak Shaver Inventory — single category model (admin)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Registry\Registry;

class CategoryModel extends AdminModel
{
    public $typeAlias = 'com_ysinventory.category';

    protected $formName = 'category';

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_ysinventory.' . $this->formName,
            $this->formName,
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }

        if (!$this->canEditState((object) $data)) {
            $form->setFieldAttribute('published', 'disabled', 'true');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        // Parent field should not expose the internal root row and must not allow self-reference.
        $recordId = 0;

        if (\is_array($data) && !empty($data['id'])) {
            $recordId = (int) $data['id'];
        } elseif (\is_object($data) && !empty($data->id)) {
            $recordId = (int) $data->id;
        } else {
            $recordId = (int) $this->getState($this->getName() . '.id');
        }

        $query = 'SELECT id AS value, title AS text, level'
            . ' FROM #__ysi_categories'
            . ' WHERE id > 1 AND published IN (0, 1)';

        if ($recordId > 0) {
            $query .= ' AND id <> ' . $recordId;
        }

        $query .= ' ORDER BY lft';
        $form->setFieldAttribute('parent_id', 'query', $query);

        return $form;
    }

    protected function loadFormData()
    {
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_ysinventory.edit.category.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            if (\is_object($data) && !empty($data->params) && \is_string($data->params)) {
                $data->params = new Registry($data->params);
            }
        }

        $this->preprocessData('com_ysinventory.category', $data);

        return $data;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item && \is_object($item)) {
            // Keep top-level categories user-facing as "no parent"; internal root remains hidden.
            if ((int) ($item->parent_id ?? 0) === 1) {
                $item->parent_id = 0;
            }

            if (!empty($item->params) && \is_string($item->params)) {
                $item->params = new Registry($item->params);
            }
        }

        return $item;
    }

    protected function prepareTable($table)
    {
        $date = Factory::getDate()->toSql();

        $this->ensureRootCategoryNode();

        $table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
        $table->generateAlias();

        if (empty($table->id)) {
            $table->created_time = $date;

            // Set the new category to be the last child of its parent.
            $parentId = (int) $table->parent_id;

            if ($parentId <= 0) {
                $parentId = 1;
            }

            $table->setLocation($parentId, 'last-child');
        } else {
            $table->modified_time    = $date;
            $table->modified_user_id = $this->getCurrentUser()->id;
        }

        if (empty($table->params)) {
            $table->params = '{}';
        } elseif ($table->params instanceof Registry) {
            $table->params = $table->params->toString();
        }
    }

    public function save($data)
    {
        $this->ensureRootCategoryNode();

        $table = $this->getTable();
        $pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $isNew = true;

        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
        }

        // Set location in the tree.
        if (isset($data['parent_id'])) {
            $parentId = (int) $data['parent_id'];

            if ($parentId <= 0) {
                $parentId = 1;
            }

            if ($isNew || $parentId != $table->parent_id) {
                $table->setLocation($parentId, 'last-child');
            }
        }

        // Encode params to JSON if passed as array.
        if (isset($data['params']) && \is_array($data['params'])) {
            $data['params'] = (new Registry($data['params']))->toString();
        }

        return parent::save($data);
    }

    /**
     * Ensure the nested-set root node exists before calling setLocation()/store().
     * This protects against partial or drifted schema/data states on reinstall/upgrade.
     */
    protected function ensureRootCategoryNode(): void
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'lft', 'rgt']))
            ->from($db->quoteName('#__ysi_categories'))
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query);
        $root = $db->loadObject();

        if (!$root) {
            $insert = $db->getQuery(true)
                ->insert($db->quoteName('#__ysi_categories'))
                ->columns(
                    [
                        $db->quoteName('id'),
                        $db->quoteName('parent_id'),
                        $db->quoteName('lft'),
                        $db->quoteName('rgt'),
                        $db->quoteName('level'),
                        $db->quoteName('path'),
                        $db->quoteName('title'),
                        $db->quoteName('alias'),
                        $db->quoteName('published'),
                        $db->quoteName('access'),
                        $db->quoteName('language'),
                        $db->quoteName('params'),
                    ]
                )
                ->values(
                    implode(
                        ',',
                        [
                            '1',
                            '0',
                            '0',
                            '1',
                            '0',
                            $db->quote(''),
                            $db->quote('ROOT'),
                            $db->quote('root'),
                            '1',
                            '1',
                            $db->quote('*'),
                            $db->quote('{}'),
                        ]
                    )
                );
            $db->setQuery($insert);
            $db->execute();

            return;
        }

        // Recover from invalid nested-set bounds in the root row.
        if ((int) $root->rgt <= (int) $root->lft) {
            $table = $this->getTable();
            $table->rebuild();
        }
    }

    protected function canDelete($record)
    {
        if (empty($record->id) || $record->published != -2) {
            return false;
        }

        return $this->getCurrentUser()->authorise('core.delete', 'com_ysinventory.category.' . (int) $record->id);
    }

    protected function canEditState($record)
    {
        $user = $this->getCurrentUser();

        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_ysinventory.category.' . (int) $record->id);
        }

        return $user->authorise('core.edit.state', 'com_ysinventory');
    }

    public function rebuild()
    {
        $table = $this->getTable();

        if (!$table->rebuild()) {
            $this->setError($table->getError());

            return false;
        }

        return true;
    }

    protected function generateNewTitle($parentId, $alias, $title)
    {
        $table = $this->getTable();
        $db    = $this->getDatabase();

        while ($table->load(['alias' => $alias, 'parent_id' => $parentId])) {
            $title = \Joomla\String\StringHelper::increment($title);
            $alias = \Joomla\String\StringHelper::increment($alias, 'dash');
        }

        return [$title, $alias];
    }
}
