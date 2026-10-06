<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Dashboard statistics model.
 */
class DashboardModel extends BaseDatabaseModel
{
	/**
	 * @return  object
	 */
	public function getStats(): object
	{
		$db = $this->getDatabase();

		$count = function (string $table, string $where = '1') use ($db) {
			return (int) $db->setQuery(
				$db->getQuery(true)
					->select('COUNT(*)')
					->from($db->quoteName($table))
					->where($where)
			)->loadResult();
		};

		return (object) [
			'courses'     => $count('#__cyonima_courses'),
			'lessons'     => $count('#__cyonima_lessons'),
			'enrollments' => $count('#__cyonima_enrollments'),
			'certificates' => $count('#__cyonima_certificates'),
			'exams'       => $count('#__cyonima_exams'),
			'paths'       => $count('#__cyonima_learning_paths'),
		];
	}
}
