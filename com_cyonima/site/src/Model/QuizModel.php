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
 * Shared behaviour for graded quizzes: exams and QCM assignments.
 */
abstract class QuizModel extends BaseDatabaseModel
{
	/**
	 * @var  object|null
	 */
	protected $quiz;

	/**
	 * The graded item (exam or assignment row).
	 *
	 * @return  object|null
	 */
	abstract public function getQuiz();

	/**
	 * The questions/attempts column holding the item id ("exam_id" or "assignment_id").
	 *
	 * @return  string
	 */
	abstract protected function getParentColumn(): string;

	/**
	 * Number of attempts allowed for the current user, 0 means unlimited.
	 *
	 * @param   object  $quiz  The graded item.
	 *
	 * @return  integer
	 */
	protected function getAttemptsAllowed(object $quiz): int
	{
		return (int) ($quiz->attempts_allowed ?? 1);
	}

	/**
	 * @return  array
	 */
	public function getQuestions(): array
	{
		$db   = $this->getDatabase();
		$quiz = $this->getQuiz();

		if (!$quiz) {
			return [];
		}

		$column = $this->getParentColumn();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__cyonima_questions'))
			->where($db->quoteName($column) . ' = :parent')
			->where($db->quoteName('published') . ' = 1')
			->bind(':parent', $quiz->id, ParameterType::INTEGER)
			->order($db->quoteName('ordering') . ' ASC');

		if (!empty($quiz->shuffle)) {
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
	 * Creates a new attempt for the current user.
	 *
	 * @return  integer  The attempt id.
	 */
	public function startAttempt(): int
	{
		$user = Factory::getApplication()->getIdentity();
		$db   = $this->getDatabase();
		$quiz = $this->getQuiz();

		if (!$quiz || $user->guest) {
			return 0;
		}

		if (!$this->canAttempt()) {
			return 0;
		}

		$data = [
			'exam_id'       => 0,
			'assignment_id' => 0,
			'user_id'       => (int) $user->id,
			'started'       => Factory::getDate()->toSql(),
			'status'        => 'in_progress',
			'answers'       => '{}',
		];

		$data[$this->getParentColumn()] = (int) $quiz->id;

		$table = $this->getMVCFactory()->createTable('ExamAttempt');
		$table->bind($data);
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
		$quiz = $this->getQuiz();

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

		$passMark = (int) ($quiz->pass_mark ?? 0);
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
		$quiz = $this->getQuiz();

		if (!$quiz || $user->guest) {
			return [];
		}

		$db     = $this->getDatabase();
		$column = $this->getParentColumn();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_exam_attempts'))
				->where($db->quoteName($column) . ' = :parent')
				->where($db->quoteName('user_id') . ' = :user')
				->bind(':parent', $quiz->id, ParameterType::INTEGER)
				->bind(':user', $user->id, ParameterType::INTEGER)
				->order($db->quoteName('id') . ' DESC')
		)->loadObjectList() ?: [];
	}

	/**
	 * Whether the current user may still attempt the quiz.
	 *
	 * @return  boolean
	 */
	public function canAttempt(): bool
	{
		$quiz = $this->getQuiz();

		if (!$quiz) {
			return false;
		}

		$attempts = $this->getAttempts();
		$allowed  = $this->getAttemptsAllowed($quiz);

		if ($allowed <= 0) {
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
	protected function isCorrect(string $type, $submitted, $correct): bool
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
	 * Mark the associated lesson (and course progress) complete after an attempt.
	 *
	 * @return  void
	 */
	protected function markLessonComplete(): void
	{
		$quiz = $this->getQuiz();

		if (!$quiz || empty($quiz->lesson_id)) {
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
				->bind(':course', $quiz->course_id, ParameterType::INTEGER)
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
				->bind(':lesson', $quiz->lesson_id, ParameterType::INTEGER)
		)->loadObject();

		$table = $this->getMVCFactory()->createTable('LessonProgress');

		if ($progress) {
			$table->load($progress->id);
		}

		$table->bind([
			'enrollment_id'  => (int) $enrollment->id,
			'lesson_id'      => (int) $quiz->lesson_id,
			'user_id'        => (int) $user->id,
			'status'         => 'completed',
			'completed_date' => Factory::getDate()->toSql(),
		]);

		$table->store();

		ProgressHelper::recalculate((int) $enrollment->id);
	}
}
