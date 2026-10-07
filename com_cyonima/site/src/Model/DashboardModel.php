<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Model for the current student's enrollments and progress.
 */
class DashboardModel extends ListModel
{
	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$user  = Factory::getApplication()->getIdentity();

		$query->select(
			[
				$db->quoteName('e.id'),
				$db->quoteName('e.progress'),
				$db->quoteName('e.score'),
				$db->quoteName('e.max_score'),
				$db->quoteName('e.status'),
				$db->quoteName('e.enrolled_date'),
				$db->quoteName('e.completed_date'),
				$db->quoteName('c.id', 'course_id'),
				$db->quoteName('c.title'),
				$db->quoteName('c.alias'),
				$db->quoteName('c.image'),
			]
		)
			->from($db->quoteName('#__cyonima_enrollments', 'e'))
			->innerJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('e.course_id'))
			->where($db->quoteName('e.user_id') . ' = ' . (int) $user->id)
			->order($db->quoteName('e.enrolled_date') . ' DESC');

		return $query;
	}
}
