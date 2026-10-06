<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Single learning path edit model.
 */
class LearningpathModel extends BaseDatabaseModel
{
	public function getItem(int $id = 0)
	{
		$id = $id ?: (int) $this->getState('learningpath.id');

		if (!$id) {
			return (object) [
				'id'          => 0,
				'title'       => '',
				'description' => '',
				'published'   => 1,
				'params'      => '{}',
			];
		}

		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_learning_paths'))
				->where($db->quoteName('id') . ' = ' . $id)
		)->loadObject();
	}

	public function save(array $data)
	{
		$table = $this->getMVCFactory()->createTable('Learningpath');

		if (!empty($data['id'])) {
			$table->load((int) $data['id']);
		}

		if (!$table->save($data)) {
			$this->setError($table->getError());

			return false;
		}

		$id = (int) $table->id;
		$this->setState('learningpath.id', $id);

		$db = $this->getDatabase();

		$db->setQuery(
			$db->getQuery(true)
				->delete($db->quoteName('#__cyonima_learning_path_courses'))
				->where($db->quoteName('path_id') . ' = :path')
				->bind(':path', $id, ParameterType::INTEGER)
		)->execute();

		$courses = $data['courses'] ?? [];

		foreach ($courses as $ordering => $courseId) {
			$courseId = (int) $courseId;

			if (!$courseId) {
				continue;
			}

			$pivot = $this->getMVCFactory()->createTable('LearningpathCourse');
			$pivot->bind([
				'path_id'  => $id,
				'course_id' => $courseId,
				'ordering' => (int) $ordering + 1,
			]);
			$pivot->store();
		}

		return $id;
	}

	/**
	 * @return  array
	 */
	public function getCourseIds(int $id = 0): array
	{
		$id = $id ?: (int) $this->getState('learningpath.id');
		$db = $this->getDatabase();

		return array_map(
			'intval',
			$db->setQuery(
				$db->getQuery(true)
					->select($db->quoteName('course_id'))
					->from($db->quoteName('#__cyonima_learning_path_courses'))
					->where($db->quoteName('path_id') . ' = :path')
					->bind(':path', $id, ParameterType::INTEGER)
					->order($db->quoteName('ordering') . ' ASC')
			)->loadColumn() ?: []
		);
	}

	/**
	 * All published courses (for the multi-select).
	 *
	 * @return  array
	 */
	public function getAllCourses(): array
	{
		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('id'),
						$db->quoteName('title'),
					]
				)
				->from($db->quoteName('#__cyonima_courses'))
				->where($db->quoteName('published') . ' = 1')
				->order($db->quoteName('title') . ' ASC')
		)->loadObjectList() ?: [];
	}
}
