<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Cyonima\Component\Cyonima\Administrator\Helper\ProgressHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Single question edit model.
 */
class QuestionModel extends BaseDatabaseModel
{
	public function getItem(int $id = 0)
	{
		$id = $id ?: (int) $this->getState('question.id');

		if (!$id) {
			return (object) [
				'id'            => 0,
				'exam_id'       => (int) $this->getState('question.exam_id', 0),
				'assignment_id' => (int) $this->getState('question.assignment_id', 0),
				'question'      => '',
				'type'          => 'single',
				'options'       => '[]',
				'answer'        => '0',
				'points'        => 1,
				'ordering'      => 0,
				'published'     => 1,
			];
		}

		$db = $this->getDatabase();

		$item = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_questions'))
				->where($db->quoteName('id') . ' = ' . $id)
		)->loadObject();

		return $item ?: null;
	}

	/**
	 * Saves a question submitted from the edit form.
	 *
	 * Expected data shape:
	 * - parent: "exam:{id}" or "assignment:{id}"
	 * - options: list of ['text' => string]
	 * - correct: map of option key => 1 for the checked (correct) options
	 *
	 * @param   array  $data  Form data.
	 *
	 * @return  integer|boolean  The question id or false on error.
	 */
	public function save(array $data)
	{
		$type = $data['type'] ?? 'single';

		$parent = (string) ($data['parent'] ?? '');
		$examId = 0;
		$assignmentId = 0;

		if (\preg_match('/^exam:(\d+)$/', $parent, $matches)) {
			$examId = (int) $matches[1];
		} elseif (\preg_match('/^assignment:(\d+)$/', $parent, $matches)) {
			$assignmentId = (int) $matches[1];
		}

		if (!$examId && !$assignmentId) {
			$this->setError(Text::_('COM_CYONIMA_ERROR_QUESTION_NO_PARENT'));

			return false;
		}

		$correctInput = $data['correct'] ?? [];
		$correctKeys  = [];

		if (\is_array($correctInput)) {
			foreach ($correctInput as $key => $value) {
				if ($value) {
					$correctKeys[] = (int) $key;
				}
			}
		}

		$rows    = \is_array($data['options'] ?? null) ? $data['options'] : [];
		$options = [];
		$answer  = [];

		foreach ($rows as $key => $row) {
			$text = trim((string) (\is_array($row) ? ($row['text'] ?? '') : $row));

			if ($text === '') {
				continue;
			}

			$index = \count($options);
			$options[] = $text;

			if (\in_array((int) $key, $correctKeys, true)) {
				$answer[] = $index;
			}
		}

		if (\count($options) < 2) {
			$this->setError(Text::sprintf('COM_CYONIMA_ERROR_QUESTION_MIN_OPTIONS', 2));

			return false;
		}

		if ($type === 'multiple') {
			if ($answer === []) {
				$this->setError(Text::_('COM_CYONIMA_ERROR_QUESTION_NO_ANSWER'));

				return false;
			}
		} elseif (\count($answer) !== 1) {
			$this->setError(Text::_('COM_CYONIMA_ERROR_QUESTION_ONE_ANSWER'));

			return false;
		}

		$data['exam_id']       = $examId;
		$data['assignment_id'] = $assignmentId;
		$data['options']       = $options;
		$data['answer']        = $type === 'multiple' ? $answer : $answer[0];
		$data['points']        = max(1, (int) ($data['points'] ?? 1));

		unset($data['parent'], $data['correct'], $data['options_text'], $data['answer_text']);

		$table = $this->getMVCFactory()->createTable('Question');

		if (!empty($data['id'])) {
			$table->load((int) $data['id']);
		}

		if (!$table->save($data)) {
			$this->setError($table->getError());

			return false;
		}

		$this->setState('question.id', (int) $table->id);

		ProgressHelper::recalculateCourse($this->getCourseId($table->id));

		return (int) $table->id;
	}

	/**
	 * Returns the id of the course owning the given question, 0 when unknown.
	 *
	 * @param   integer  $questionId  Question id.
	 *
	 * @return  integer
	 */
	private function getCourseId(int $questionId): int
	{
		$db = $this->getDatabase();

		$question = $db->setQuery(
			$db->getQuery(true)
				->select([$db->quoteName('exam_id'), $db->quoteName('assignment_id')])
				->from($db->quoteName('#__cyonima_questions'))
				->where($db->quoteName('id') . ' = ' . $questionId)
		)->loadObject();

		if (!$question) {
			return 0;
		}

		$courseId = 0;

		if (!empty($question->exam_id)) {
			$courseId = (int) $db->setQuery(
				$db->getQuery(true)
					->select($db->quoteName('course_id'))
					->from($db->quoteName('#__cyonima_exams'))
					->where($db->quoteName('id') . ' = ' . (int) $question->exam_id)
			)->loadResult();
		} elseif (!empty($question->assignment_id)) {
			$courseId = (int) $db->setQuery(
				$db->getQuery(true)
					->select($db->quoteName('course_id'))
					->from($db->quoteName('#__cyonima_assignments'))
					->where($db->quoteName('id') . ' = ' . (int) $question->assignment_id)
			)->loadResult();
		}

		return $courseId;
	}
}
