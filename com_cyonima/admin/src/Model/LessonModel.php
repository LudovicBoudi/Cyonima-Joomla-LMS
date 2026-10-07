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
 * Single lesson edit model.
 */
class LessonModel extends BaseDatabaseModel
{
	public function getItem(int $id = 0)
	{
		$id = $id ?: (int) $this->getState('lesson.id');

		if (!$id) {
			return (object) [
				'id'          => 0,
				'course_id'   => (int) $this->getState('lesson.course_id'),
				'title'       => '',
				'type'        => 'content',
				'published'   => 1,
				'access'      => 1,
				'params'      => '{}',
				'content'     => '',
				'url'         => '',
				'media'       => '',
				'duration'    => 0,
				'max_score'   => 0,
			];
		}

		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_lessons'))
				->where($db->quoteName('id') . ' = ' . $id)
		)->loadObject();
	}

	public function save(array $data)
	{
		$table = $this->getMVCFactory()->createTable('Lesson');

		if (!empty($data['id'])) {
			$table->load((int) $data['id']);
		}

		if (!$table->save($data)) {
			$this->setError($table->getError());

			return false;
		}

		$this->setState('lesson.id', (int) $table->id);

		return (int) $table->id;
	}
}
