<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Helper;

\defined('_JEXEC') or die;

use Cyonima\Component\Cyonima\Administrator\Table\CertificateTable;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

/**
 * Issues personalised completion certificates.
 */
class CertificateHelper
{
	/**
	 * Issue a certificate for a completed course when not already issued.
	 *
	 * @param   integer  $courseId  Course id.
	 * @param   integer  $userId    Student user id.
	 *
	 * @return  integer|null  The certificate id, or null when unavailable.
	 */
	public static function issue(int $courseId, int $userId): ?int
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

		$existing = (int) $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__cyonima_certificates'))
				->where($db->quoteName('course_id') . ' = ' . $courseId)
				->where($db->quoteName('user_id') . ' = ' . $userId)
		)->loadResult();

		if ($existing) {
			return $existing;
		}

		$course = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_courses'))
				->where($db->quoteName('id') . ' = ' . $courseId)
		)->loadObject();

		$student = new User($userId);

		if (!$course || !$student || !$student->id) {
			return null;
		}

		$template = self::findTemplate((int) $course->created_by, $courseId);

		$params       = CyonimaHelper::getParams();
		$number       = $params->get('certificate_prefix', 'CYN') . '-' . $courseId . '-' . $userId . '-' . date('Ymd');
		$issued       = Factory::getDate()->toSql();
		$relativeDir  = trim($params->get('certificate_path', 'images/com_cyonima/certificates'), '/');
		$fileName     = 'certificate_' . $courseId . '_' . $userId . '.png';
		$absolutePath = JPATH_ROOT . '/' . $relativeDir . '/' . $fileName;
		$filePath     = $relativeDir . '/' . $fileName;

		if ($template) {
			$templatePath = JPATH_ROOT . '/' . trim($template->image, '/');
			$positions    = (new Registry($template->params))->toArray();

			$placeholders = [
				'name'   => trim($student->name),
				'course' => $course->title,
				'date'   => Factory::getDate($issued)->format('d/m/Y'),
				'number' => $number,
			];

			try {
				CertificateGenerator::generate($templatePath, $placeholders, $positions, $absolutePath);
			} catch (\Throwable $e) {
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			}
		}

		/** @var CertificateTable $certificate */
		$certificate = new CertificateTable($db);
		$certificate->bind([
			'course_id'          => $courseId,
			'user_id'            => $userId,
			'template_id'        => $template ? (int) $template->id : 0,
			'certificate_number' => $number,
			'issued_date'        => $issued,
			'file_path'          => $filePath,
			'params'             => '{}',
		]);
		$certificate->store();

		return (int) $certificate->id;
	}

	/**
	 * Find a matching certificate template: course specific, then teacher default.
	 *
	 * @param   integer  $teacherId  Teacher user id.
	 * @param   integer  $courseId   Course id.
	 *
	 * @return  object|null
	 */
	private static function findTemplate(int $teacherId, int $courseId)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

		foreach ([[$courseId, 0], [0, $teacherId]] as [$courseFilter, $userFilter]) {
			$query = $db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_certificate_templates'))
				->where($db->quoteName('published') . ' = 1')
				->where($db->quoteName('course_id') . ' = ' . $courseFilter)
				->where($db->quoteName('user_id') . ' = ' . $userFilter)
				->order($db->quoteName('id') . ' DESC');

			$db->setQuery($query, 0, 1);

			if ($row = $db->loadObject()) {
				return $row;
			}
		}

		return null;
	}
}
