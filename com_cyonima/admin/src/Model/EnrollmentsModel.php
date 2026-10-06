<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\Database\ParameterType;

/**
 * Enrollments list model.
 */
class EnrollmentsModel extends BaseCyonimaListModel
{
	protected function populateState($ordering = 'e.enrolled_date', $direction = 'DESC')
	{
		$course = $this->getUserStateFromRequest($this->context . '.filter.course', 'filter_course', '');
		$this->setState('filter.course', $course);

		parent::populateState($ordering, $direction);
	}

	public function getTable($name = '', $prefix = '', $options = [])
	{
		return $this->getMVCFactory()->createTable('Enrollment');
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('e.id'),
				$db->quoteName('e.status'),
				$db->quoteName('e.progress'),
				$db->quoteName('e.enrolled_date'),
				$db->quoteName('e.completed_date'),
				$db->quoteName('c.title', 'course_title'),
				$db->quoteName('u.name', 'student'),
			]
		)
			->from($db->quoteName('#__cyonima_enrollments', 'e'))
			->innerJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('e.course_id'))
			->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('e.user_id'));

		$course = $this->getState('filter.course');

		if (is_numeric($course)) {
			$query->where($db->quoteName('e.course_id') . ' = :course')
				->bind(':course', $course, ParameterType::INTEGER);
		}

		$query->order($db->quoteName('e.enrolled_date') . ' DESC');

		return $query;
	}
}
