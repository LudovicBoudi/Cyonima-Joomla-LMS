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
 * Lessons list model.
 */
class LessonsModel extends BaseCyonimaListModel
{
	protected function populateState($ordering = 'l.course_id', $direction = 'ASC')
	{
		$course = $this->getUserStateFromRequest($this->context . '.filter.course', 'filter_course', '');
		$this->setState('filter.course', $course);

		parent::populateState($ordering, $direction);
	}

	public function getTable($name = '', $prefix = '', $options = [])
	{
		return $this->getMVCFactory()->createTable('Lesson');
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.course');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('l.id'),
				$db->quoteName('l.title'),
				$db->quoteName('l.type'),
				$db->quoteName('l.ordering'),
				$db->quoteName('l.published'),
				$db->quoteName('c.title', 'course_title'),
			]
		)
			->from($db->quoteName('#__cyonima_lessons', 'l'))
			->leftJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('l.course_id'));

		$course = $this->getState('filter.course');

		if (is_numeric($course)) {
			$query->where($db->quoteName('l.course_id') . ' = :course')
				->bind(':course', $course, ParameterType::INTEGER);
		}

		$query->order($db->quoteName('l.course_id') . ' ASC, ' . $db->quoteName('l.ordering') . ' ASC');

		return $query;
	}
}
