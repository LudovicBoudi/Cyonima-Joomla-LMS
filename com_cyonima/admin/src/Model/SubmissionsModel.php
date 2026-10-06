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
 * Submissions list model (grading).
 */
class SubmissionsModel extends BaseCyonimaListModel
{
	protected function populateState($ordering = 's.submitted_date', $direction = 'DESC')
	{
		$assignment = $this->getUserStateFromRequest($this->context . '.filter.assignment', 'filter_assignment', '');
		$this->setState('filter.assignment', $assignment);

		parent::populateState($ordering, $direction);
	}

	public function getTable($name = '', $prefix = '', $options = [])
	{
		return $this->getMVCFactory()->createTable('Submission');
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('s.id'),
				$db->quoteName('s.user_id'),
				$db->quoteName('s.submitted_date'),
				$db->quoteName('s.status'),
				$db->quoteName('s.score'),
				$db->quoteName('s.file_name'),
				$db->quoteName('u.name', 'student'),
				$db->quoteName('a.title', 'assignment_title'),
				$db->quoteName('a.max_score'),
			]
		)
			->from($db->quoteName('#__cyonima_submissions', 's'))
			->innerJoin($db->quoteName('#__cyonima_assignments', 'a') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('s.assignment_id'))
			->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'));

		$assignment = $this->getState('filter.assignment');

		if (is_numeric($assignment)) {
			$query->where($db->quoteName('s.assignment_id') . ' = :assignment')
				->bind(':assignment', $assignment, ParameterType::INTEGER);
		}

		$query->order($db->quoteName('s.submitted_date') . ' DESC');

		return $query;
	}
}
