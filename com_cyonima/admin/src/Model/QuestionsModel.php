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
			]
		)
			->from($db->quoteName('#__cyonima_questions', 'q'));

		$exam = $this->getState('filter.exam');

		if (is_numeric($exam)) {
			$query->where($db->quoteName('q.exam_id') . ' = :exam')
				->bind(':exam', $exam, ParameterType::INTEGER);
		}

		$query->order($db->quoteName('q.ordering') . ' ASC');

		return $query;
	}
}
