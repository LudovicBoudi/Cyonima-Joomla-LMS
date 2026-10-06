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
 * Assignments list model.
 */
class AssignmentsModel extends BaseCyonimaListModel
{
	protected function populateState($ordering = 'a.title', $direction = 'ASC')
	{
		$course = $this->getUserStateFromRequest($this->context . '.filter.course', 'filter_course', '');
		$this->setState('filter.course', $course);

		parent::populateState($ordering, $direction);
	}

	public function getTable($name = '', $prefix = '', $options = [])
	{
		return $this->getMVCFactory()->createTable('Assignment');
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('a.id'),
				$db->quoteName('a.title'),
				$db->quoteName('a.max_score'),
				$db->quoteName('a.due_date'),
				$db->quoteName('a.published'),
				$db->quoteName('c.title', 'course_title'),
			]
		)
			->from($db->quoteName('#__cyonima_assignments', 'a'))
			->leftJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.course_id'));

		$course = $this->getState('filter.course');

		if (is_numeric($course)) {
			$query->where($db->quoteName('a.course_id') . ' = :course')
				->bind(':course', $course, ParameterType::INTEGER);
		}

		$query->order($db->quoteName('a.title') . ' ASC');

		return $query;
	}
}
