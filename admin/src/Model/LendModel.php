<?php

/**
 * Yak Shaver Inventory — single lend model (admin)
 *
 * @package     YakShaver\Component\Ysinventory
 * @subpackage  Administrator
 * @author      Yak Shaver <me@kayakshaver.com>
 * @copyright   (C) 2026 Yak Shaver https://www.kayakshaver.com
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace YakShaver\Component\Ysinventory\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class LendModel extends AdminModel
{
    public $typeAlias = 'com_ysinventory.lend';

    protected $formName = 'lend';

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

        // Use the actual record for authorization when $data is empty (form bootstrap).
        $stateCheckData = $data;

        if (empty($stateCheckData) || empty($stateCheckData['ysi_item_id'] ?? null)) {
            $stateCheckData = $this->getItem();
        }

        if (!$this->canEditState((object) $stateCheckData)) {
            $form->setFieldAttribute('ysi_status', 'disabled', 'true');
            $form->setFieldAttribute('ysi_status', 'filter', 'unset');
        }

        return $form;
    }

    protected function loadFormData()
    {
        $app = Factory::getApplication();
        $data = $app->getUserState('com_ysinventory.edit.lend.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_ysinventory.lend', $data);

        return $data;
    }

    protected function prepareTable($table)
    {
        $date = Factory::getDate()->toSql();

        if (empty($table->id)) {
            $table->created = $date;
        } else {
            $table->modified = $date;
            $table->modified_by = $this->getCurrentUser()->id;
        }
    }

    protected function canDelete($record)
    {
        if (empty($record->id)) {
            return false;
        }

        return $this->getCurrentUser()->authorise('core.delete', 'com_ysinventory');
    }

    protected function canEditState($record)
    {
        $user = $this->getCurrentUser();

        // Component ACL check.
        if ($user->authorise('core.edit.state', 'com_ysinventory')) {
            return true;
        }

        // Category-level moderation group check with component fallback.
        $itemId = (int) ($record->ysi_item_id ?? 0);

        if ($itemId <= 0 || $user->guest) {
            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('catid'))
            ->from($db->quoteName('#__ysi_items'))
            ->where($db->quoteName('id') . ' = :itemId')
            ->bind(':itemId', $itemId, ParameterType::INTEGER);
        $db->setQuery($query);
        $catId = (int) $db->loadResult();

        $moderationGroups = [];

        if ($catId > 0) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__ysi_categories'))
                ->where($db->quoteName('id') . ' = :catId')
                ->bind(':catId', $catId, ParameterType::INTEGER);
            $db->setQuery($query);
            $catJson = $db->loadResult();

            if (!empty($catJson) && \is_string($catJson)) {
                $catParams        = new Registry($catJson);
                $moderationGroups = (array) $catParams->get('ysi_lend_moderation_groups', []);
            }
        }

        if (empty($moderationGroups)) {
            $moderationGroups = (array) ComponentHelper::getParams('com_ysinventory')->get('ysi_lend_moderation_groups', []);
        }

        if (!empty($moderationGroups)) {
            $userGroups = $user->getAuthorisedGroups();

            if (!empty(array_intersect($userGroups, $moderationGroups))) {
                return true;
            }
        }

        return false;
    }
}
