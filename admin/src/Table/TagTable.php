<?php

/**
 * Yak Shaver Inventory — tag table class
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
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

class TagTable extends Table implements CurrentUserInterface
{
    use CurrentUserTrait;

    protected $_supportNullValue = false;

    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_ysinventory.tag';

        parent::__construct('#__ysi_tags', 'id', $db, $dispatcher);

        $this->setColumnAlias('title', 'name');
    }

    public function store($updateNulls = true)
    {
        $date = Factory::getDate()->toSql();
        $userId = $this->getCurrentUser()->id;

        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->id) {
            $this->modified_by = $userId;
            $this->modified = $date;
        } else {
            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }

            if (!(int) $this->modified) {
                $this->modified = $date;
            }

            if (empty($this->modified_by)) {
                $this->modified_by = $userId;
            }
        }

        // Verify alias uniqueness.
        $table = new self($this->getDatabase(), $this->getDispatcher());

        if ($table->load(['alias' => $this->alias]) && ($table->id != $this->id || $this->id == 0)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_TAG_UNIQUE_ALIAS'));

            if ($table->published === -2) {
                $this->setError(Text::_('COM_YSINVENTORY_ERROR_TAG_UNIQUE_ALIAS_TRASHED'));
            }

            return false;
        }

        return parent::store($updateNulls);
    }

    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (trim($this->name) == '') {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_PROVIDE_VALID_NAME'));

            return false;
        }

        if (empty($this->ysi_tag_group_id)) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_TAG_REQUIRE_GROUP'));

            return false;
        }

        // Verify the referenced tag group exists.
        $db = $this->getDatabase();
        $groupId = (int) $this->ysi_tag_group_id;
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__ysi_tag_groups'))
            ->where($db->quoteName('id') . ' = :groupId')
            ->bind(':groupId', $groupId, \Joomla\Database\ParameterType::INTEGER);
        $db->setQuery($query);

        if ((int) $db->loadResult() === 0) {
            $this->setError(Text::_('COM_YSINVENTORY_ERROR_TAG_GROUP_NOT_FOUND'));

            return false;
        }

        $this->generateAlias();

        if (empty($this->id)) {
            $this->hits = 0;
        }

        return true;
    }

    public function generateAlias()
    {
        if (empty($this->alias)) {
            $this->alias = $this->name;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language ?? '');

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return $this->alias;
    }

    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
