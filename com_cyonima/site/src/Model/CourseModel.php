<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

/**
 * Model for a single course with its lessons and the current user's enrollment.
 */
class CourseModel extends BaseDatabaseModel
{
	/**
	 * @var  object|null
	 */
	private $course;

	/**
	 * @var  object|null
	 */
	private $enrollment;

	/**
	 * Returns the course row.
	 *
	 * @return  object|null
	 */
	public function getCourse()
	{
		if ($this->course !== null) {
			return $this->course;
		}

		$db = $this->getDatabase();
		$id = (int) $this->getState('course.id');

		if (!$id) {
			return null;
		}

		$this->course = $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('c.*'),
						$db->quoteName('u.name', 'teacher'),
					]
				)
				->from($db->quoteName('#__cyonima_courses', 'c'))
				->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('c.created_by'))
				->where($db->quoteName('c.id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		return $this->course;
	}

	/**
	 * Returns the lessons of the course.
	 *
	 * @return  array
	 */
	public function getLessons(): array
	{
		$db   = $this->getDatabase();
		$id   = (int) $this->getState('course.id');

		if (!$id) {
			return [];
		}

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_lessons'))
				->where($db->quoteName('course_id') . ' = :id')
				->where($db->quoteName('published') . ' = 1')
				->bind(':id', $id, ParameterType::INTEGER)
				->order($db->quoteName('ordering') . ' ASC')
		)->loadObjectList() ?: [];
	}

	/**
	 * Returns the current user's enrollment for the course.
	 *
	 * @return  object|null
	 */
	public function getEnrollment()
	{
		if ($this->enrollment !== null) {
			return $this->enrollment;
		}

		$user = Factory::getApplication()->getIdentity();

		if ($user->guest) {
			return null;
		}

		$db = $this->getDatabase();
		$id = (int) $this->getState('course.id');

		$this->enrollment = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_enrollments'))
				->where($db->quoteName('course_id') . ' = :course')
				->where($db->quoteName('user_id') . ' = :user')
				->bind(':course', $id, ParameterType::INTEGER)
				->bind(':user', $user->id, ParameterType::INTEGER)
		)->loadObject();

		return $this->enrollment;
	}

	/**
	 * Returns lesson completion state keyed by lesson id.
	 *
	 * @return  array
	 */
	public function getCompletedLessons(): array
	{
		$enrollment = $this->getEnrollment();

		if (!$enrollment) {
			return [];
		}

		$db = $this->getDatabase();

		$rows = $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('lesson_id'),
						$db->quoteName('status'),
					]
				)
				->from($db->quoteName('#__cyonima_lesson_progress'))
				->where($db->quoteName('enrollment_id') . ' = :enrollment')
				->bind(':enrollment', $enrollment->id, ParameterType::INTEGER)
		)->loadAssocList();

		$map = [];

		foreach ($rows ?: [] as $row) {
			$map[(int) $row['lesson_id']] = $row['status'];
		}

		return $map;
	}
}
