<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Model for a single learning path with its ordered courses.
 */
class LearningpathModel extends BaseDatabaseModel
{
	/**
	 * @var  object|null
	 */
	private $path;

	/**
	 * @return  object|null
	 */
	public function getPath()
	{
		if ($this->path !== null) {
			return $this->path;
		}

		$db = $this->getDatabase();
		$id = (int) $this->getState('learningpath.id');

		if (!$id) {
			return null;
		}

		$this->path = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_learning_paths'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		return $this->path;
	}

	/**
	 * @return  array
	 */
	public function getCourses(): array
	{
		$db = $this->getDatabase();
		$id = (int) $this->getState('learningpath.id');

		if (!$id) {
			return [];
		}

		return $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('c.id'),
						$db->quoteName('c.title'),
						$db->quoteName('c.alias'),
						$db->quoteName('c.intro'),
						$db->quoteName('c.image'),
						$db->quoteName('lpc.ordering'),
					]
				)
				->from($db->quoteName('#__cyonima_learning_path_courses', 'lpc'))
				->innerJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('lpc.course_id'))
				->where($db->quoteName('lpc.path_id') . ' = :id')
				->where($db->quoteName('c.published') . ' = 1')
				->bind(':id', $id, ParameterType::INTEGER)
				->order($db->quoteName('lpc.ordering') . ' ASC')
		)->loadObjectList() ?: [];
	}
}
