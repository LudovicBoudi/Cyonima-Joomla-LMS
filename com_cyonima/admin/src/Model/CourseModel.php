<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;

/**
 * Single course edit model.
 */
class CourseModel extends BaseDatabaseModel
{
	public function getItem(int $id = 0)
	{
		$id = $id ?: (int) $this->getState('course.id');

		if (!$id) {
			return (object) [
				'id'          => 0,
				'title'       => '',
				'published'   => 1,
				'featured'    => 0,
				'access'      => 1,
				'language'    => '*',
				'params'      => '{}',
				'intro'       => '',
				'description' => '',
			];
		}

		$db = $this->getDatabase();

		$item = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_courses'))
				->where($db->quoteName('id') . ' = ' . $id)
		)->loadObject();

		return $item ?: null;
	}

	public function save(array $data)
	{
		$table = $this->getMVCFactory()->createTable('Course');

		if (!empty($data['id'])) {
			$table->load((int) $data['id']);
		}

		if (!$table->bind($data)) {
			$this->setError($table->getError());

			return false;
		}

		if (!$table->check()) {
			$this->setError($table->getError());

			return false;
		}

		if (!$table->store()) {
			$this->setError($table->getError());

			return false;
		}

		$this->setState('course.id', (int) $table->id);

		return (int) $table->id;
	}
}
