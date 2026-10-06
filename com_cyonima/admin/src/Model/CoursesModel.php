<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

/**
 * Courses list model.
 */
class CoursesModel extends BaseCyonimaListModel
{
	protected function populateState($ordering = 'c.title', $direction = 'ASC')
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		parent::populateState($ordering, $direction);
	}

	public function getTable($name = '', $prefix = '', $options = [])
	{
		return $this->getMVCFactory()->createTable('Course');
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('c.id'),
				$db->quoteName('c.title'),
				$db->quoteName('c.published'),
				$db->quoteName('c.featured'),
				$db->quoteName('c.ordering'),
				$db->quoteName('c.created'),
				$db->quoteName('c.created_by'),
				$db->quoteName('c.hits'),
				$db->quoteName('u.name', 'teacher'),
			]
		)
			->from($db->quoteName('#__cyonima_courses', 'c'))
			->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('c.created_by'));

		$search = $this->getState('filter.search');

		if (!empty($search)) {
			$search = '%' . trim($search) . '%';
			$query->where($db->quoteName('c.title') . ' LIKE :search')
				->bind(':search', $search);
		}

		$published = $this->getState('filter.published');

		if (is_numeric($published)) {
			$query->where($db->quoteName('c.published') . ' = :published')
				->bind(':published', $published, ParameterType::INTEGER);
		}

		$ordering  = $this->getState('list.ordering', 'c.title');
		$direction = $this->getState('list.direction', 'ASC');

		$query->order($db->quoteName($ordering) . ' ' . $direction);

		return $query;
	}
}
