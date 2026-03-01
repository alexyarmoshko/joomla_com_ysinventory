<?php

/**
 * Yak Shaver Inventory — single location model (admin)
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

class LocationModel extends AdminModel
{
    public $typeAlias = 'com_ysinventory.location';

    protected $formName = 'location';

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
        $app = Factory::getApplication();
        $data = $app->getUserState('com_ysinventory.edit.location.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_ysinventory.location', $data);

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
                    ->from($db->quoteName('#__ysi_locations'));
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            $table->modified = $date;
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
}
