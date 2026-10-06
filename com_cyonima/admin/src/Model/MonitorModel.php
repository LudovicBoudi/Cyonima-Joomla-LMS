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
 * Monitoring model: per-course student progress and results.
 */
class MonitorModel extends BaseDatabaseModel
{
	/**
	 * @return  object|null
	 */
	public function getCourse(int $id = 0)
	{
		$id = $id ?: (int) $this->getState('monitor.course_id');
		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_courses'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();
	}

	/**
	 * @return  array
	 */
	public function getStudents(int $courseId = 0): array
	{
		$courseId = $courseId ?: (int) $this->getState('monitor.course_id');
		$db       = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('u.id', 'user_id'),
						$db->quoteName('u.name', 'student'),
						$db->quoteName('u.email'),
						$db->quoteName('e.progress'),
						$db->quoteName('e.status'),
						$db->quoteName('e.enrolled_date'),
						$db->quoteName('e.completed_date'),
					]
				)
				->from($db->quoteName('#__cyonima_enrollments', 'e'))
				->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('e.user_id'))
				->where($db->quoteName('e.course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
				->order($db->quoteName('u.name') . ' ASC')
		)->loadObjectList() ?: [];
	}

	/**
	 * Per-student assignment/exam results within a course.
	 *
	 * @param   integer  $courseId  Course id.
	 *
	 * @return  array
	 */
	public function getResults(int $courseId = 0): array
	{
		$courseId = $courseId ?: (int) $this->getState('monitor.course_id');
		$db       = $this->getDatabase();

		$assignments = $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('s.user_id'),
						$db->quoteName('a.title'),
						$db->quoteName('s.score'),
						$db->quoteName('a.max_score'),
						$db->quoteName('s.status'),
					]
				)
				->from($db->quoteName('#__cyonima_submissions', 's'))
				->innerJoin($db->quoteName('#__cyonima_assignments', 'a') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('s.assignment_id'))
				->where($db->quoteName('a.course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
		)->loadObjectList() ?: [];

		$exams = $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('t.user_id'),
						$db->quoteName('e.title'),
						$db->quoteName('t.score'),
						$db->quoteName('t.max_score'),
						$db->quoteName('t.passed'),
					]
				)
				->from($db->quoteName('#__cyonima_exam_attempts', 't'))
				->innerJoin($db->quoteName('#__cyonima_exams', 'e') . ' ON ' . $db->quoteName('e.id') . ' = ' . $db->quoteName('t.exam_id'))
				->where($db->quoteName('e.course_id') . ' = :course')
				->where($db->quoteName('t.status') . ' = ' . $db->quote('finished'))
				->bind(':course', $courseId, ParameterType::INTEGER)
		)->loadObjectList() ?: [];

		return ['assignments' => $assignments, 'exams' => $exams];
	}
}
