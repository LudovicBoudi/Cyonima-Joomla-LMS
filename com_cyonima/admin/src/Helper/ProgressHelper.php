<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Computes and persists a student's progress and weighted grade within a course.
 */
class ProgressHelper
{
	/**
	 * Recalculate progress and grade for an enrollment.
	 *
	 * @param   integer  $enrollmentId  The enrollment id.
	 *
	 * @return  void
	 */
	public static function recalculate(int $enrollmentId): void
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$enrollment = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_enrollments'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $enrollmentId, ParameterType::INTEGER)
		)->loadObject();

		if (!$enrollment) {
			return;
		}

		$total = (int) $db->setQuery(
			$db->getQuery(true)
				->select('COUNT(*)')
				->from($db->quoteName('#__cyonima_lessons'))
				->where($db->quoteName('course_id') . ' = :course')
				->where($db->quoteName('published') . ' = 1')
				->bind(':course', $enrollment->course_id, ParameterType::INTEGER)
		)->loadResult();

		$completed = (int) $db->setQuery(
			$db->getQuery(true)
				->select('COUNT(*)')
				->from($db->quoteName('#__cyonima_lesson_progress', 'p'))
				->innerJoin($db->quoteName('#__cyonima_lessons', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.lesson_id'))
				->where($db->quoteName('p.enrollment_id') . ' = :enrollment')
				->where($db->quoteName('p.status') . ' = ' . $db->quote('completed'))
				->where($db->quoteName('l.published') . ' = 1')
				->bind(':enrollment', $enrollmentId, ParameterType::INTEGER)
		)->loadResult();

		$progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
		$now      = Factory::getDate()->toSql();

		$status        = 'enrolled';
		$completedDate = $enrollment->completed_date;

		if ($progress >= 100) {
			$status        = 'completed';
			$completedDate = $completedDate ?: $now;
		} elseif ($progress > 0) {
			$status = 'in_progress';
		}

		$grade = self::calculateGrade((int) $enrollment->course_id, (int) $enrollment->user_id);

		$score    = $grade === null ? 0.0 : (float) $grade;
		$maxScore = $grade === null ? 0.0 : 100.0;

		$db->setQuery(
			$db->getQuery(true)
				->update($db->quoteName('#__cyonima_enrollments'))
				->set($db->quoteName('progress') . ' = :progress')
				->set($db->quoteName('status') . ' = :status')
				->set($db->quoteName('completed_date') . ' = :completed')
				->set($db->quoteName('score') . ' = :score')
				->set($db->quoteName('max_score') . ' = :maxscore')
				->where($db->quoteName('id') . ' = :id')
				->bind(':progress', $progress, ParameterType::INTEGER)
				->bind(':status', $status)
				->bind(':completed', $completedDate)
				->bind(':score', $score, ParameterType::STRING)
				->bind(':maxscore', $maxScore, ParameterType::STRING)
				->bind(':id', $enrollmentId, ParameterType::INTEGER)
		)->execute();

		if ($status === 'completed') {
			CertificateHelper::issue((int) $enrollment->course_id, (int) $enrollment->user_id);
		}
	}

	/**
	 * Recalculate progress and grade for every enrollment of a course.
	 *
	 * @param   integer  $courseId  The course id.
	 *
	 * @return  void
	 */
	public static function recalculateCourse(int $courseId): void
	{
		if (!$courseId) {
			return;
		}

		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$ids = $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__cyonima_enrollments'))
				->where($db->quoteName('course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
		)->loadColumn() ?: [];

		foreach ($ids as $id) {
			self::recalculate((int) $id);
		}
	}

	/**
	 * Weighted average (in percent) of every graded quiz of a course for a user.
	 *
	 * Each exam and assignment with questions counts with its own coefficient:
	 * note = sum(percent x coefficient) / sum(coefficient).
	 * A published quiz without attempt counts as 0%, a quiz without questions
	 * (or with a zero coefficient) is left out of the average.
	 *
	 * @param   integer  $courseId  The course id.
	 * @param   integer  $userId    The user id.
	 *
	 * @return  float|null  The grade out of 100, null when the course has no graded quiz.
	 */
	public static function calculateGrade(int $courseId, int $userId): ?float
	{
		$db   = Factory::getContainer()->get(DatabaseInterface::class);
		$rows = [];

		$definitions = [
			'exams'       => 'exam_id',
			'assignments' => 'assignment_id',
		];

		foreach ($definitions as $table => $column) {
			$quizzes = $db->setQuery(
				$db->getQuery(true)
					->select([$db->quoteName('id'), $db->quoteName('coefficient')])
					->from($db->quoteName('#__cyonima_' . $table))
					->where($db->quoteName('course_id') . ' = :course')
					->where($db->quoteName('published') . ' = 1')
					->bind(':course', $courseId, ParameterType::INTEGER)
			)->loadObjectList() ?: [];

			if (!$quizzes) {
				continue;
			}

			$ids = array_map('intval', array_column($quizzes, 'id'));
			$in  = implode(',', $ids);

			$points = [];

			$pointRows = $db->setQuery(
				$db->getQuery(true)
					->select([$db->quoteName($column, 'qid'), 'SUM(' . $db->quoteName('points') . ') AS ' . $db->quoteName('total_points')])
					->from($db->quoteName('#__cyonima_questions'))
					->where($db->quoteName('published') . ' = 1')
					->where($db->quoteName($column) . ' IN (' . $in . ')')
					->group($db->quoteName($column))
			)->loadObjectList() ?: [];

			foreach ($pointRows as $pointRow) {
				$points[(int) $pointRow->qid] = (float) $pointRow->total_points;
			}

			$best = [];

			$attemptRows = $db->setQuery(
				$db->getQuery(true)
					->select([$db->quoteName($column, 'qid'), 'MAX(' . $db->quoteName('score') . ' / NULLIF(' . $db->quoteName('max_score') . ', 0) * 100) AS ' . $db->quoteName('pct')])
					->from($db->quoteName('#__cyonima_exam_attempts'))
					->where($db->quoteName('user_id') . ' = :user')
					->where($db->quoteName('status') . ' = ' . $db->quote('finished'))
					->where($db->quoteName($column) . ' IN (' . $in . ')')
					->group($db->quoteName($column))
					->bind(':user', $userId, ParameterType::INTEGER)
			)->loadObjectList() ?: [];

			foreach ($attemptRows as $attemptRow) {
				$best[(int) $attemptRow->qid] = (float) $attemptRow->pct;
			}

			foreach ($quizzes as $quiz) {
				$rows[] = [
					'coefficient' => (float) $quiz->coefficient,
					'points'      => $points[(int) $quiz->id] ?? 0.0,
					'percent'     => $best[(int) $quiz->id] ?? null,
				];
			}
		}

		$sum    = 0.0;
		$weight = 0.0;

		foreach ($rows as $row) {
			// Not graded: no coefficient or no published question to answer.
			if ($row['coefficient'] <= 0 || $row['points'] <= 0) {
				continue;
			}

			// A missing attempt counts as 0%.
			$sum    += ($row['percent'] ?? 0.0) * $row['coefficient'];
			$weight += $row['coefficient'];
		}

		return $weight > 0 ? round($sum / $weight, 2) : null;
	}
}
