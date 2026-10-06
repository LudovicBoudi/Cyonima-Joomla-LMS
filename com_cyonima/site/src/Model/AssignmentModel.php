<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Model for an assignment and the current user's submission.
 */
class AssignmentModel extends BaseDatabaseModel
{
	/**
	 * @var  object|null
	 */
	private $assignment;

	/**
	 * @return  object|null
	 */
	public function getAssignment()
	{
		if ($this->assignment !== null) {
			return $this->assignment;
		}

		$db = $this->getDatabase();
		$id = (int) $this->getState('assignment.id');

		if (!$id) {
			return null;
		}

		$this->assignment = $db->setQuery(
			$db->getQuery(true)
				->select(
					[
						$db->quoteName('a.*'),
						$db->quoteName('c.title', 'course_title'),
						$db->quoteName('c.alias', 'course_alias'),
					]
				)
				->from($db->quoteName('#__cyonima_assignments', 'a'))
				->leftJoin($db->quoteName('#__cyonima_courses', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.course_id'))
				->where($db->quoteName('a.id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		return $this->assignment;
	}

	/**
	 * @return  object|null
	 */
	public function getSubmission()
	{
		$user = Factory::getApplication()->getIdentity();

		if ($user->guest) {
			return null;
		}

		$db = $this->getDatabase();
		$id = (int) $this->getState('assignment.id');

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_submissions'))
				->where($db->quoteName('assignment_id') . ' = :assignment')
				->where($db->quoteName('user_id') . ' = :user')
				->bind(':assignment', $id, ParameterType::INTEGER)
				->bind(':user', $user->id, ParameterType::INTEGER)
		)->loadObject();
	}

	/**
	 * Save a submission for the current user.
	 *
	 * @param   string  $content    Text answer.
	 * @param   string  $fileName   Uploaded file name.
	 *
	 * @return  boolean
	 */
	public function submit(string $content, string $fileName = ''): bool
	{
		$user = Factory::getApplication()->getIdentity();
		$db   = $this->getDatabase();

		$assignment = $this->getAssignment();

		if (!$assignment || $user->guest) {
			return false;
		}

		$existing = $this->getSubmission();

		$data = [
			'id'             => $existing->id ?? 0,
			'assignment_id'  => (int) $assignment->id,
			'user_id'        => (int) $user->id,
			'content'        => $content,
			'file_name'      => $fileName,
			'submitted_date' => Factory::getDate()->toSql(),
			'status'         => 'submitted',
		];

		/** @var \Cyonima\Component\Cyonima\Administrator\Table\SubmissionTable $table */
		$table = $this->getMVCFactory()->createTable('Submission');

		if (!$table->save($data)) {
			$this->setError($table->getError());

			return false;
		}

		return true;
	}
}
