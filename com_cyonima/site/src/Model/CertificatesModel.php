<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Model;

\defined('_JEXEC') or die;

use Cyonima\Component\Cyonima\Administrator\Helper\CertificateHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Model for the current user's certificates.
 */
class CertificatesModel extends BaseDatabaseModel
{
	/**
	 * Returns the current user's certificates.
	 *
	 * @return  array
	 */
	public function getItems(): array
	{
		$user = Factory::getApplication()->getIdentity();
		$db   = $this->getDatabase();

		$items = $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('cert') . '.*',
						$db->quoteName('c.title', 'course_title'),
					]
				)
				->from($db->quoteName('#__cyonima_certificates', 'cert'))
				->innerJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('cert.course_id'))
				->where($db->quoteName('cert.user_id') . ' = :user')
				->bind(':user', $user->id, ParameterType::INTEGER)
				->order($db->quoteName('cert.issued_date') . ' DESC')
		)->loadObjectList() ?: [];

		foreach ($items as $item) {
			$item->file_exists = is_file(JPATH_ROOT . '/' . trim($item->file_path, '/'));
		}

		return $items;
	}

	/**
	 * Returns a single certificate owned by the current user.
	 *
	 * @param   integer  $id  Certificate id.
	 *
	 * @return  object|null
	 */
	public function getItem(int $id)
	{
		$user = Factory::getApplication()->getIdentity();
		$db   = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_certificates'))
				->where($db->quoteName('id') . ' = :id')
				->where($db->quoteName('user_id') . ' = :user')
				->bind(':id', $id, ParameterType::INTEGER)
				->bind(':user', $user->id, ParameterType::INTEGER)
		)->loadObject();
	}

	/**
	 * Ensures a certificate exists for the course and returns it.
	 *
	 * @param   integer  $courseId  Course id.
	 *
	 * @return  object|null
	 */
	public function ensure(int $courseId)
	{
		$user = Factory::getApplication()->getIdentity();

		CertificateHelper::issue($courseId, (int) $user->id);

		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_certificates'))
				->where($db->quoteName('course_id') . ' = :course')
				->where($db->quoteName('user_id') . ' = :user')
				->bind(':course', $courseId, ParameterType::INTEGER)
				->bind(':user', $user->id, ParameterType::INTEGER)
		)->loadObject();
	}
}
