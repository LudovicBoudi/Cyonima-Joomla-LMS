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
 * Questions list model.
 */
class QuestionsModel extends BaseCyonimaListModel
{
	protected function populateState($ordering = 'q.ordering', $direction = 'ASC')
	{
		$exam = $this->getUserStateFromRequest($this->context . '.filter.exam', 'filter_exam', '');
		$this->setState('filter.exam', $exam);

		$assignment = $this->getUserStateFromRequest($this->context . '.filter.assignment', 'filter_assignment', '');
		$this->setState('filter.assignment', $assignment);

		parent::populateState($ordering, $direction);
	}

	public function getTable($name = '', $prefix = '', $options = [])
	{
		return $this->getMVCFactory()->createTable('Question');
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('q.id'),
				$db->quoteName('q.question'),
				$db->quoteName('q.type'),
				$db->quoteName('q.points'),
				$db->quoteName('q.ordering'),
				$db->quoteName('q.published'),
				$db->quoteName('q.exam_id'),
				$db->quoteName('q.assignment_id'),
			]
		)
			->select(
				'COALESCE(' . $db->quoteName('e.title') . ', ' . $db->quoteName('a.title') . ', ' . $db->quote('') . ') AS ' . $db->quoteName('parent_title')
			)
			->from($db->quoteName('#__cyonima_questions', 'q'))
			->leftJoin($db->quoteName('#__cyonima_exams', 'e') . ' ON ' . $db->quoteName('e.id') . ' = ' . $db->quoteName('q.exam_id'))
			->leftJoin($db->quoteName('#__cyonima_assignments', 'a') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('q.assignment_id'));

		$exam = $this->getState('filter.exam');

		if (is_numeric($exam)) {
			$query->where($db->quoteName('q.exam_id') . ' = :exam')
				->bind(':exam', $exam, ParameterType::INTEGER);
		}

		$assignment = $this->getState('filter.assignment');

		if (is_numeric($assignment)) {
			$query->where($db->quoteName('q.assignment_id') . ' = :assignment')
				->bind(':assignment', $assignment, ParameterType::INTEGER);
		}

		$query->order($db->quoteName('q.ordering') . ' ASC');

		return $query;
	}
}
