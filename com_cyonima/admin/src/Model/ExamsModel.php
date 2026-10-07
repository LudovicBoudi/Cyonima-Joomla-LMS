<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;


/**
 * Exams list model.
 */
class ExamsModel extends BaseCyonimaListModel
{
	protected function populateState($ordering = 'e.title', $direction = 'ASC')
	{
		parent::populateState($ordering, $direction);
	}

	public function getTable($name = '', $prefix = '', $options = [])
	{
		return $this->getMVCFactory()->createTable('Exam');
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('e.id'),
				$db->quoteName('e.title'),
				$db->quoteName('e.pass_mark'),
				$db->quoteName('e.time_limit'),
				$db->quoteName('e.coefficient'),
				$db->quoteName('e.published'),
				$db->quoteName('c.title', 'course_title'),
			]
		)
			->from($db->quoteName('#__cyonima_exams', 'e'))
			->leftJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('e.course_id'))
			->order($db->quoteName('e.title') . ' ASC');

		return $query;
	}
}
