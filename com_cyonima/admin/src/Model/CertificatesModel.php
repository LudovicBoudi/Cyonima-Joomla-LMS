<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;


/**
 * Issued certificates list model.
 */
class CertificatesModel extends BaseCyonimaListModel
{
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return $this->getMVCFactory()->createTable('Certificate');
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('cert.id'),
				$db->quoteName('cert.certificate_number'),
				$db->quoteName('cert.issued_date'),
				$db->quoteName('c.title', 'course_title'),
				$db->quoteName('u.name', 'student'),
			]
		)
			->from($db->quoteName('#__cyonima_certificates', 'cert'))
			->innerJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('cert.course_id'))
			->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('cert.user_id'))
			->order($db->quoteName('cert.issued_date') . ' DESC');

		return $query;
	}
}
