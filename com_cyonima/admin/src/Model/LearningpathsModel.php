<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;


/**
 * Learning paths list model.
 */
class LearningpathsModel extends BaseCyonimaListModel
{
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return $this->getMVCFactory()->createTable('Learningpath');
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('id'),
				$db->quoteName('title'),
				$db->quoteName('published'),
				$db->quoteName('ordering'),
				$db->quoteName('created'),
			]
		)
			->from($db->quoteName('#__cyonima_learning_paths'))
			->order($db->quoteName('ordering') . ' ASC');

		return $query;
	}
}
