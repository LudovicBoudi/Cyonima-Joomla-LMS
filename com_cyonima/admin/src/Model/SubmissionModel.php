<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;

/**
 * Grading model for a single submission.
 */
class SubmissionModel extends BaseDatabaseModel
{
	public function getItem(int $id = 0)
	{
		$id = $id ?: (int) $this->getState('submission.id');
		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('s.*'),
						$db->quoteName('u.name', 'student'),
						$db->quoteName('a.title', 'assignment_title'),
						$db->quoteName('a.max_score'),
					]
				)
				->from($db->quoteName('#__cyonima_submissions', 's'))
				->innerJoin($db->quoteName('#__cyonima_assignments', 'a') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('s.assignment_id'))
				->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('s.user_id'))
				->where($db->quoteName('s.id') . ' = ' . $id)
		)->loadObject();
	}

	public function grade(int $id, $score, string $feedback): bool
	{
		$table = $this->getMVCFactory()->createTable('Submission');
		$table->load($id);

		if (!$table->id) {
			$this->setError('Submission not found');

			return false;
		}

		$table->score       = (float) $score;
		$table->feedback    = $feedback;
		$table->status      = 'graded';
		$table->graded_by   = (int) Factory::getApplication()->getIdentity()->id;
		$table->graded_date = Factory::getDate()->toSql();

		if (!$table->store()) {
			$this->setError($table->getError());

			return false;
		}

		return true;
	}
}
