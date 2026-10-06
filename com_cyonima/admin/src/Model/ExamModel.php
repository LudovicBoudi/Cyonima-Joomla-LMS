<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Single exam edit model.
 */
class ExamModel extends BaseDatabaseModel
{
	public function getItem(int $id = 0)
	{
		$id = $id ?: (int) $this->getState('exam.id');

		if (!$id) {
			return (object) [
				'id'               => 0,
				'course_id'        => 0,
				'lesson_id'        => 0,
				'title'            => '',
				'description'      => '',
				'time_limit'       => 0,
				'pass_mark'        => 50,
				'attempts_allowed' => 1,
				'shuffle'          => 0,
				'published'        => 1,
				'params'           => '{}',
			];
		}

		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_exams'))
				->where($db->quoteName('id') . ' = ' . $id)
		)->loadObject();
	}

	public function save(array $data)
	{
		$table = $this->getMVCFactory()->createTable('Exam');

		if (!empty($data['id'])) {
			$table->load((int) $data['id']);
		}

		if (!$table->save($data)) {
			$this->setError($table->getError());

			return false;
		}

		$this->setState('exam.id', (int) $table->id);

		return (int) $table->id;
	}
}
