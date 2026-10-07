<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Model;

\defined('_JEXEC') or die;

use Joomla\Database\ParameterType;

/**
 * Model for taking and grading a QCM assignment.
 */
class AssignmentModel extends QuizModel
{
	/**
	 * @return  object|null
	 */
	public function getQuiz()
	{
		if ($this->quiz !== null) {
			return $this->quiz;
		}

		$db = $this->getDatabase();
		$id = (int) $this->getState('assignment.id');

		if (!$id) {
			return null;
		}

		$this->quiz = $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('a') . '.*',
						$db->quoteName('c.title', 'course_title'),
						$db->quoteName('c.alias', 'course_alias'),
					]
				)
				->from($db->quoteName('#__cyonima_assignments', 'a'))
				->leftJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.course_id'))
				->where($db->quoteName('a.id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		return $this->quiz;
	}

	/**
	 * @return  object|null
	 */
	public function getAssignment()
	{
		return $this->getQuiz();
	}

	/**
	 * @return  string
	 */
	protected function getParentColumn(): string
	{
		return 'assignment_id';
	}
}
