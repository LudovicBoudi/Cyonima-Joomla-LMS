<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

/**
 * Model for the published course catalogue.
 */
class CoursesModel extends ListModel
{
	protected function populateState($ordering = 'c.title', $direction = 'ASC')
	{
		$app = Factory::getApplication();

		$this->setState('list.limit', $app->getParams()->get('courses_per_page', 12));

		parent::populateState($ordering, $direction);
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('c.id'),
				$db->quoteName('c.title'),
				$db->quoteName('c.alias'),
				$db->quoteName('c.intro'),
				$db->quoteName('c.image'),
				$db->quoteName('c.created'),
				$db->quoteName('c.featured'),
				$db->quoteName('u.name', 'teacher'),
			]
		)
			->from($db->quoteName('#__cyonima_courses', 'c'))
			->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('c.created_by'))
			->where($db->quoteName('c.published') . ' = 1');

		$ordering  = $this->getState('list.ordering', 'c.title');
		$direction = $this->getState('list.direction', 'ASC');

		$query->order($db->quoteName($ordering) . ' ' . $direction);

		return $query;
	}
}
