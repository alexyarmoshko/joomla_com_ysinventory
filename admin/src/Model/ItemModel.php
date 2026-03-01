<?php

/**
 * Yak Shaver Inventory — single item model (admin)
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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\ParameterType;

class ItemModel extends AdminModel
{
    public $typeAlias = 'com_ysinventory.item';

    protected $formName = 'item';

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
            $form->setFieldAttribute('ordering', 'disabled', 'true');

            $form->setFieldAttribute('published', 'filter', 'unset');
            $form->setFieldAttribute('ordering', 'filter', 'unset');
        }

        return $form;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item && $item->id) {
            // Load associated tag IDs from the mapping table.
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('ysi_tag_id'))
                ->from($db->quoteName('#__ysi_item_tag_map'))
                ->where($db->quoteName('ysi_item_id') . ' = :itemId')
                ->bind(':itemId', $item->id, ParameterType::INTEGER);
            $db->setQuery($query);

            $item->ysi_tags = $db->loadColumn();
        } elseif ($item) {
            $item->ysi_tags = [];
        }

        return $item;
    }

    protected function loadFormData()
    {
        $app = Factory::getApplication();
        $data = $app->getUserState('com_ysinventory.edit.item.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_ysinventory.item', $data);

        return $data;
    }

    protected function prepareTable($table)
    {
        $date = Factory::getDate()->toSql();

        $table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
        $table->generateAlias();

        if (empty($table->id)) {
            $table->created = $date;

            if (empty($table->ordering)) {
                $db = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select('MAX(ordering)')
                    ->from($db->quoteName('#__ysi_items'));
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            $table->modified = $date;
            $table->modified_by = $this->getCurrentUser()->id;
        }
    }

    public function save($data)
    {
        // Extract tags before parent save (they are not a table column).
        $tags = isset($data['ysi_tags']) ? (array) $data['ysi_tags'] : [];
        unset($data['ysi_tags']);

        if (!parent::save($data)) {
            return false;
        }

        // Sync item–tag mapping.
        $itemId = (int) $this->getState($this->getName() . '.id');
        $db = $this->getDatabase();

        // Delete existing mappings.
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__ysi_item_tag_map'))
            ->where($db->quoteName('ysi_item_id') . ' = :itemId')
            ->bind(':itemId', $itemId, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        // Insert new mappings.
        if (!empty($tags)) {
            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__ysi_item_tag_map'))
                ->columns($db->quoteName(['ysi_item_id', 'ysi_tag_id']));

            foreach ($tags as $tagId) {
                $tagId = (int) $tagId;

                if ($tagId > 0) {
                    $query->values($itemId . ', ' . $tagId);
                }
            }

            $db->setQuery($query);
            $db->execute();
        }

        return true;
    }

    protected function canDelete($record)
    {
        if (empty($record->id) || $record->published != -2) {
            return false;
        }

        return $this->getCurrentUser()->authorise('core.delete', 'com_ysinventory');
    }

    protected function canEditState($record)
    {
        return $this->getCurrentUser()->authorise('core.edit.state', 'com_ysinventory');
    }

    public function publish(&$pks, $value = 1)
    {
        if ((int) $value === 2) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_ARCHIVE_NOT_SUPPORTED'));

            return false;
        }

        return parent::publish($pks, $value);
    }
}
