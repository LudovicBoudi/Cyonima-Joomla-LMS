<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Model for a single lesson.
 */
class LessonModel extends BaseDatabaseModel
{
	/**
	 * @var  object|null
	 */
	private $lesson;

	/**
	 * Returns the lesson row.
	 *
	 * @return  object|null
	 */
	public function getLesson()
	{
		if ($this->lesson !== null) {
			return $this->lesson;
		}

		$db = $this->getDatabase();
		$id = (int) $this->getState('lesson.id');

		if (!$id) {
			return null;
		}

		$this->lesson = $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('l.*'),
						$db->quoteName('c.title', 'course_title'),
						$db->quoteName('c.alias', 'course_alias'),
					]
				)
				->from($db->quoteName('#__cyonima_lessons', 'l'))
				->leftJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('l.course_id'))
				->where($db->quoteName('l.id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		if ($this->lesson) {
			$this->lesson->assignment_id = 0;
			$this->lesson->exam_id       = 0;

			if ($this->lesson->type === 'assignment') {
				$this->lesson->assignment_id = (int) $db->setQuery(
					$db->getQuery(true)
						->select($db->quoteName('id'))
						->from($db->quoteName('#__cyonima_assignments'))
						->where($db->quoteName('lesson_id') . ' = ' . $id)
				)->loadResult();
			} elseif ($this->lesson->type === 'exam') {
				$this->lesson->exam_id = (int) $db->setQuery(
					$db->getQuery(true)
						->select($db->quoteName('id'))
						->from($db->quoteName('#__cyonima_exams'))
						->where($db->quoteName('lesson_id') . ' = ' . $id)
				)->loadResult();
			}
		}

		return $this->lesson;
	}
}
