<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Exam table.
 */
class ExamTable extends Table
{
	/**
	 * @param   DatabaseDriver  $db  Database driver.
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__cyonima_exams', 'id', $db);
	}

	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && \is_array($array['params'])) {
			$array['params'] = json_encode($array['params']);
		}

		return parent::bind($array, $ignore);
	}

	public function check()
	{
		if (trim($this->title) === '') {
			$this->setError(\Joomla\CMS\Language\Text::_('COM_CYONIMA_ERROR_TITLE_REQUIRED'));

			return false;
		}

		return true;
	}

	public function store($updateNulls = true)
	{
		$date = Factory::getDate();
		$user   = Factory::getApplication()->getIdentity();
		$userId = $user ? (int) $user->id : 0;
		$now    = $date->toSql();

		if ($this->id) {
			$this->modified = $now;
		} else {
			if (!(int) $this->created) {
				$this->created = $now;
			}

			if (empty($this->created_by)) {
				$this->created_by = $userId;
			}

			$this->modified = $now;
		}

		return parent::store($updateNulls);
	}
}
