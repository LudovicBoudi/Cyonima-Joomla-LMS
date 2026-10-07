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
 * Model for taking and grading a formal exam.
 */
class ExamModel extends QuizModel
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
		$id = (int) $this->getState('exam.id');

		if (!$id) {
			return null;
		}

		$this->quiz = $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('e') . '.*',
						$db->quoteName('c.title', 'course_title'),
						$db->quoteName('c.alias', 'course_alias'),
					]
				)
				->from($db->quoteName('#__cyonima_exams', 'e'))
				->leftJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('e.course_id'))
				->where($db->quoteName('e.id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		return $this->quiz;
	}

	/**
	 * @return  object|null
	 */
	public function getExam()
	{
		return $this->getQuiz();
	}

	/**
	 * @return  string
	 */
	protected function getParentColumn(): string
	{
		return 'exam_id';
	}
}
