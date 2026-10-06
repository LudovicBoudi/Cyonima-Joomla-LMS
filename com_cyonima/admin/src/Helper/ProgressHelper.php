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
 * Computes and persists a student's progress within a course.
 */
class ProgressHelper
{
	/**
	 * Recalculate progress for an enrollment based on lesson completion.
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

		$db->setQuery(
			$db->getQuery(true)
				->update($db->quoteName('#__cyonima_enrollments'))
				->set($db->quoteName('progress') . ' = :progress')
				->set($db->quoteName('status') . ' = :status')
				->set($db->quoteName('completed_date') . ' = :completed')
				->where($db->quoteName('id') . ' = :id')
				->bind(':progress', $progress, ParameterType::INTEGER)
				->bind(':status', $status)
				->bind(':completed', $completedDate)
				->bind(':id', $enrollmentId, ParameterType::INTEGER)
		)->execute();

		if ($status === 'completed') {
			CertificateHelper::issue((int) $enrollment->course_id, (int) $enrollment->user_id);
		}
	}
}
