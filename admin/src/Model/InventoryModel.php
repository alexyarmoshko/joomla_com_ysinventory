<?php

/**
 * Yak Shaver Inventory — single inventory model (admin)
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

class InventoryModel extends AdminModel
{
    public $typeAlias = 'com_ysinventory.inventory';

    protected $formName = 'inventory';

    public function save($data)
    {
        if (!\is_array($data)) {
            return parent::save($data);
        }

        $data = $this->normalizeOwnerData($data);

        return parent::save($data);
    }

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

    protected function loadFormData()
    {
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_ysinventory.edit.inventory.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        if (\is_object($data)) {
            if (
                empty($data->ysi_contact_user_id)
                && empty($data->ysi_contact_contact_id)
                && !empty($data->ysi_contact_id)
            ) {
                $data->ysi_contact_user_id = (int) $data->ysi_contact_id;
            }
        } elseif (\is_array($data)) {
            if (
                empty($data['ysi_contact_user_id'])
                && empty($data['ysi_contact_contact_id'])
                && !empty($data['ysi_contact_id'])
            ) {
                $data['ysi_contact_user_id'] = (int) $data['ysi_contact_id'];
            }
        }

        $this->preprocessData('com_ysinventory.inventory', $data);

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
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select('MAX(ordering)')
                    ->from($db->quoteName('#__ysi_inventories'));
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            $table->modified    = $date;
            $table->modified_by = $this->getCurrentUser()->id;
        }
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

    private function normalizeOwnerData(array $data): array
    {
        $postData = Factory::getApplication()->getInput()->post->get('jform', [], 'array');

        $data['ysi_contact_user_id'] = $this->extractOwnerId(
            $data['ysi_contact_user_id'] ?? ($postData['ysi_contact_user_id'] ?? null)
        );
        $data['ysi_contact_contact_id'] = $this->extractOwnerId(
            $data['ysi_contact_contact_id'] ?? ($postData['ysi_contact_contact_id'] ?? null)
        );

        if (
            $data['ysi_contact_user_id'] === 0
            && $data['ysi_contact_contact_id'] === 0
        ) {
            $legacy = $this->extractOwnerId($data['ysi_contact_id'] ?? ($postData['ysi_contact_id'] ?? null));

            if ($legacy > 0) {
                $data['ysi_contact_user_id'] = $legacy;
            }
        }

        if (
            $data['ysi_contact_user_id'] > 0
            && $data['ysi_contact_contact_id'] > 0
        ) {
            $lastSelected = strtolower(trim((string) ($postData['ysi_owner_last_selected'] ?? ($data['ysi_owner_last_selected'] ?? ''))));

            if ($lastSelected === 'user') {
                $data['ysi_contact_contact_id'] = 0;
            } else {
                // Default precedence to contact when both values are posted.
                $data['ysi_contact_user_id'] = 0;
            }
        }

        // Keep legacy user owner value populated for backward compatibility readers.
        $data['ysi_contact_id'] = $data['ysi_contact_user_id'];
        unset($data['ysi_owner_last_selected']);

        return $data;
    }

    private function extractOwnerId($value): int
    {
        if (\is_array($value)) {
            $values = array_reverse($value);

            foreach ($values as $candidate) {
                $id = $this->extractOwnerId($candidate);

                if ($id > 0) {
                    return $id;
                }
            }

            return 0;
        }

        if (\is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return 0;
            }

            if (str_contains($trimmed, ':')) {
                [$id] = explode(':', $trimmed, 2);

                return (int) $id;
            }

            return (int) $trimmed;
        }

        return (int) $value;
    }
}
