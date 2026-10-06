<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Model;

\defined('_JEXEC') or die;

use Cyonima\Component\Cyonima\Administrator\Helper\ProgressHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Model for taking and grading a formal exam.
 */
class ExamModel extends BaseDatabaseModel
{
	/**
	 * @var  object|null
	 */
	private $exam;

	/**
	 * @return  object|null
	 */
	public function getExam()
	{
		if ($this->exam !== null) {
			return $this->exam;
		}

		$db = $this->getDatabase();
		$id = (int) $this->getState('exam.id');

		if (!$id) {
			return null;
		}

		$this->exam = $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('e.*'),
						$db->quoteName('c.title', 'course_title'),
						$db->quoteName('c.alias', 'course_alias'),
					]
				)
				->from($db->quoteName('#__cyonima_exams', 'e'))
				->leftJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('e.course_id'))
				->where($db->quoteName('e.id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		return $this->exam;
	}

	/**
	 * @return  array
	 */
	public function getQuestions(): array
	{
		$db   = $this->getDatabase();
		$exam = $this->getExam();

		if (!$exam) {
			return [];
		}

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__cyonima_questions'))
			->where($db->quoteName('exam_id') . ' = :exam')
			->where($db->quoteName('published') . ' = 1')
			->bind(':exam', $exam->id, ParameterType::INTEGER)
			->order($db->quoteName('ordering') . ' ASC');

		if (!empty($exam->shuffle)) {
			$query->order('RAND()');
		}

		$questions = $db->setQuery($query)->loadObjectList() ?: [];

		foreach ($questions as $question) {
			$question->options = json_decode($question->options, true) ?: [];
			$question->answer  = json_decode($question->answer, true);
		}

		return $questions;
	}

	/**
	 * Creates a new exam attempt for the current user.
	 *
	 * @return  integer  The attempt id.
	 */
	public function startAttempt(): int
	{
		$user = Factory::getApplication()->getIdentity();
		$db   = $this->getDatabase();
		$exam = $this->getExam();

		if (!$exam || $user->guest) {
			return 0;
		}

		$table = $this->getMVCFactory()->createTable('ExamAttempt');

		$table->bind([
			'exam_id'  => (int) $exam->id,
			'user_id'  => (int) $user->id,
			'started'  => Factory::getDate()->toSql(),
			'status'   => 'in_progress',
			'answers'  => '{}',
		]);

		$table->store();

		return (int) $table->id;
	}

	/**
	 * Grades a submitted attempt and persists the result.
	 *
	 * @param   integer  $attemptId  Attempt id.
	 * @param   array    $answers    Submitted answers keyed by question id.
	 *
	 * @return  boolean
	 */
	public function submitAttempt(int $attemptId, array $answers): bool
	{
		$user = Factory::getApplication()->getIdentity();
		$db   = $this->getDatabase();
		$exam = $this->getExam();

		$attempt = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_exam_attempts'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $attemptId, ParameterType::INTEGER)
		)->loadObject();

		if (!$attempt || (int) $attempt->user_id !== (int) $user->id || $attempt->status === 'finished') {
			return false;
		}

		$questions = $this->getQuestions();
		$score     = 0.0;
		$maxScore  = 0.0;

		foreach ($questions as $question) {
			$points = (float) $question->points;
			$maxScore += $points;

			$submitted = $answers[(int) $question->id] ?? null;
			$correct   = $question->answer;

			if ($this->isCorrect($question->type, $submitted, $correct)) {
				$score += $points;
			}
		}

		$passMark = (int) ($exam->pass_mark ?: 50);
		$percent  = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
		$passed   = $percent >= $passMark ? 1 : 0;

		$table = $this->getMVCFactory()->createTable('ExamAttempt');
		$table->load($attemptId);
		$table->bind([
			'finished'  => Factory::getDate()->toSql(),
			'score'     => $score,
			'max_score' => $maxScore,
			'passed'    => $passed,
			'answers'   => $answers,
			'status'    => 'finished',
		]);

		if (!$table->store()) {
			$this->setError($table->getError());

			return false;
		}

		$this->markLessonComplete();

		return true;
	}

	/**
	 * Returns the current user's finished attempts.
	 *
	 * @return  array
	 */
	public function getAttempts(): array
	{
		$user = Factory::getApplication()->getIdentity();
		$exam = $this->getExam();

		if (!$exam || $user->guest) {
			return [];
		}

		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_exam_attempts'))
				->where($db->quoteName('exam_id') . ' = :exam')
				->where($db->quoteName('user_id') . ' = :user')
				->bind(':exam', $exam->id, ParameterType::INTEGER)
				->bind(':user', $user->id, ParameterType::INTEGER)
				->order($db->quoteName('id') . ' DESC')
		)->loadObjectList() ?: [];
	}

	/**
	 * Whether the current user may still attempt the exam.
	 *
	 * @return  boolean
	 */
	public function canAttempt(): bool
	{
		$exam     = $this->getExam();
		$attempts = $this->getAttempts();
		$allowed  = (int) ($exam->attempts_allowed ?: 1);

		if ($allowed === 0) {
			return true;
		}

		$finished = \count(array_filter($attempts, fn ($a) => $a->status === 'finished'));

		return $finished < $allowed;
	}

	/**
	 * @param   string  $type       Question type.
	 * @param   mixed   $submitted  Submitted answer.
	 * @param   mixed   $correct    Correct answer.
	 *
	 * @return  boolean
	 */
	private function isCorrect(string $type, $submitted, $correct): bool
	{
		if ($submitted === null) {
			return false;
		}

		switch ($type) {
			case 'multiple':
				$submitted = \is_array($submitted) ? array_map('intval', $submitted) : [];
				$correct   = \is_array($correct) ? array_map('intval', $correct) : [];

				sort($submitted);
				sort($correct);

				return $submitted === $correct;
			case 'truefalse':
			case 'single':
			default:
				return (int) $submitted === (int) $correct;
		}
	}

	/**
	 * Mark the associated lesson (and course progress) complete after a passing exam.
	 *
	 * @return  void
	 */
	private function markLessonComplete(): void
	{
		$exam = $this->getExam();

		if (!$exam || empty($exam->lesson_id)) {
			return;
		}

		$user = Factory::getApplication()->getIdentity();
		$db   = $this->getDatabase();

		$enrollment = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_enrollments'))
				->where($db->quoteName('course_id') . ' = :course')
				->where($db->quoteName('user_id') . ' = :user')
				->bind(':course', $exam->course_id, ParameterType::INTEGER)
				->bind(':user', $user->id, ParameterType::INTEGER)
		)->loadObject();

		if (!$enrollment) {
			return;
		}

		$progress = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_lesson_progress'))
				->where($db->quoteName('enrollment_id') . ' = :enrollment')
				->where($db->quoteName('lesson_id') . ' = :lesson')
				->bind(':enrollment', $enrollment->id, ParameterType::INTEGER)
				->bind(':lesson', $exam->lesson_id, ParameterType::INTEGER)
		)->loadObject();

		$table = $this->getMVCFactory()->createTable('LessonProgress');

		if ($progress) {
			$table->load($progress->id);
		}

		$table->bind([
			'enrollment_id'  => (int) $enrollment->id,
			'lesson_id'      => (int) $exam->lesson_id,
			'user_id'        => (int) $user->id,
			'status'         => 'completed',
			'completed_date' => Factory::getDate()->toSql(),
		]);

		$table->store();

		ProgressHelper::recalculate((int) $enrollment->id);
	}
}
