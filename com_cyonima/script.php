<?php

/**
 * @package     Cyonima\Component\Cyonima
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Table\Usergroup;

/**
 * Installation and update script for com_cyonima.
 */
class com_cyonimaInstallerScript
{
	/**
	 * The "Teacher" user group title.
	 *
	 * @var string
	 */
	private const GROUP_TEACHER = 'Teacher';

	/**
	 * The "Student" user group title.
	 *
	 * @var string
	 */
	private const GROUP_STUDENT = 'Student';

	/**
	 * Runs after install.
	 *
	 * @param   string            $type    Install type.
	 * @param   InstallerAdapter  $parent  Parent installer.
	 *
	 * @return  boolean
	 */
	public function postflight($type, $parent): bool
	{
		if ($type !== 'install' && $type !== 'discover_install') {
			return true;
		}

		$this->createUserGroup(self::GROUP_TEACHER);
		$this->createUserGroup(self::GROUP_STUDENT);
		$this->setDefaultAcl();

		return true;
	}

	/**
	 * Removes the dedicated user groups on uninstall.
	 *
	 * @param   InstallerAdapter  $parent  Parent installer.
	 *
	 * @return  void
	 */
	public function uninstall($parent): void
	{
		$this->deleteUserGroup(self::GROUP_TEACHER);
		$this->deleteUserGroup(self::GROUP_STUDENT);
	}

	/**
	 * Creates a user group as a child of the "Registered" group when missing.
	 *
	 * @param   string  $title  Group title.
	 *
	 * @return  void
	 */
	private function createUserGroup(string $title): void
	{
		$db = $this->getDb();

		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__usergroups'))
			->where($db->quoteName('title') . ' = :title')
			->bind(':title', $title);

		$db->setQuery($query);

		if ($db->loadResult()) {
			return;
		}

		$registeredId = (int) $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__usergroups'))
				->where($db->quoteName('title') . ' = :title')
				->bind(':title', 'Registered')
		)->loadResult();

		$group           = new Usergroup($db);
		$group->title     = $title;
		$group->parent_id = $registeredId ?: 2;
		$group->store();
	}

	/**
	 * Deletes a user group by title when it exists.
	 *
	 * @param   string  $title  Group title.
	 *
	 * @return  void
	 */
	private function deleteUserGroup(string $title): void
	{
		$db = $this->getDb();

		$id = (int) $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__usergroups'))
				->where($db->quoteName('title') . ' = :title')
				->bind(':title', $title)
		)->loadResult();

		if (!$id) {
			return;
		}

		$group = new Usergroup($db);
		$group->delete($id);
	}

	/**
	 * Grants the component permissions to the Teacher and Student groups.
	 *
	 * @return  void
	 */
	private function setDefaultAcl(): void
	{
		$db = $this->getDb();

		$teacherId = $this->getGroupId(self::GROUP_TEACHER);
		$studentId = $this->getGroupId(self::GROUP_STUDENT);

		if (!$teacherId && !$studentId) {
			return;
		}

		$assetId = (int) $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__assets'))
				->where($db->quoteName('name') . ' = :name')
				->bind(':name', 'com_cyonima')
		)->loadResult();

		if (!$assetId) {
			return;
		}

		$rules = new Rules((string) $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('rules'))
				->from($db->quoteName('#__assets'))
				->where($db->quoteName('id') . ' = ' . $assetId)
		)->loadResult());

		if ($teacherId) {
			$rules->allow('core.manage', [$teacherId]);
			$rules->allow('core.create', [$teacherId]);
			$rules->allow('core.edit', [$teacherId]);
			$rules->allow('core.edit.state', [$teacherId]);
			$rules->allow('core.delete', [$teacherId]);
			$rules->allow('course.teach', [$teacherId]);
			$rules->allow('grade.submission', [$teacherId]);
		}

		if ($studentId) {
			$rules->allow('course.enroll', [$studentId]);
		}

		// Any logged-in user can enroll by default.
		$registeredId = $this->getGroupId('Registered');
		$rules->allow('course.enroll', [$registeredId ?: 2]);

		$db->setQuery(
			$db->getQuery(true)
				->update($db->quoteName('#__assets'))
				->set($db->quoteName('rules') . ' = :rules')
				->where($db->quoteName('id') . ' = ' . $assetId)
				->bind(':rules', (string) $rules)
		)->execute();
	}

	/**
	 * @param   string  $title  Group title.
	 *
	 * @return  integer
	 */
	private function getGroupId(string $title): int
	{
		$db = $this->getDb();

		return (int) $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__usergroups'))
				->where($db->quoteName('title') . ' = :title')
				->bind(':title', $title)
		)->loadResult();
	}

	/**
	 * @return  \Joomla\Database\DatabaseDriver
	 */
	private function getDb()
	{
		return Factory::getContainer()->get('DatabaseDriver');
	}
}
