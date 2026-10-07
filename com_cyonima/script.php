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
		// Idempotent setup: safe to run on install, update and discover_install.
		$this->createUserGroup(self::GROUP_TEACHER);
		$this->createUserGroup(self::GROUP_STUDENT);
		$this->setDefaultAcl();
		$this->migrateSchema();

		return true;
	}

	/**
	 * Adds columns and indexes introduced after the first release (idempotent).
	 *
	 * @return  void
	 */
	private function migrateSchema(): void
	{
		$this->addMissingColumn('#__cyonima_assignments', 'coefficient', 'DECIMAL(10,2) NOT NULL DEFAULT 1 AFTER `max_score`');
		$this->addMissingColumn('#__cyonima_assignments', 'attempts_allowed', 'INT UNSIGNED NOT NULL DEFAULT 1 AFTER `coefficient`');
		$this->addMissingColumn('#__cyonima_exams', 'coefficient', 'DECIMAL(10,2) NOT NULL DEFAULT 1 AFTER `shuffle`');
		$this->addMissingColumn('#__cyonima_questions', 'assignment_id', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `exam_id`');
		$this->addMissingColumn('#__cyonima_exam_attempts', 'assignment_id', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `exam_id`');

		$this->addMissingIndex('#__cyonima_questions', 'idx_assignment', 'assignment_id');
		$this->addMissingIndex('#__cyonima_exam_attempts', 'idx_assignment', 'assignment_id');

		$this->addMissingTable(
			'#__cyonima_sections',
			'CREATE TABLE IF NOT EXISTS `#__cyonima_sections` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `ordering` int NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 1,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `params` text NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course` (`course_id`),
  KEY `idx_state` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci'
		);

		$this->addMissingColumn('#__cyonima_lessons', 'section_id', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `course_id`');
		$this->addMissingIndex('#__cyonima_lessons', 'idx_section', 'section_id');
	}

	/**
	 * Creates the table when it is missing (idempotent).
	 *
	 * @param   string  $table  Quoted table name (with #__ prefix).
	 * @param   string  $sql    CREATE TABLE statement.
	 *
	 * @return  void
	 */
	private function addMissingTable(string $table, string $sql): void
	{
		$db   = $this->getDb();
		$name = $db->replacePrefix($table);

		if (\in_array($name, $db->getTableList(), true)) {
			return;
		}

		$db->setQuery($sql)->execute();
	}

	/**
	 * @param   string  $table       Quoted table name (with #__ prefix).
	 * @param   string  $column      Column name.
	 * @param   string  $definition  Column definition.
	 *
	 * @return  void
	 */
	private function addMissingColumn(string $table, string $column, string $definition): void
	{
		$db   = $this->getDb();
		$name = $db->replacePrefix($table);

		$columns = $db->getTableColumns($name);

		if (\array_key_exists($column, $columns)) {
			return;
		}

		$db->setQuery('ALTER TABLE `' . $name . '` ADD COLUMN `' . $column . '` ' . $definition)->execute();
	}

	/**
	 * @param   string  $table    Quoted table name (with #__ prefix).
	 * @param   string  $index    Index name.
	 * @param   string  $column   Column name.
	 *
	 * @return  void
	 */
	private function addMissingIndex(string $table, string $index, string $column): void
	{
		$db   = $this->getDb();
		$name = $db->replacePrefix($table);

		$exists = $db->setQuery(
			'SHOW INDEX FROM `' . $name . '` WHERE Key_name = ' . $db->quote($index)
		)->loadObject();

		if ($exists) {
			return;
		}

		$db->setQuery('ALTER TABLE `' . $name . '` ADD INDEX `' . $index . '` (`' . $column . '`)')->execute();
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
				->where($db->quoteName('title') . ' = ' . $db->quote('Registered'))
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
				->where($db->quoteName('name') . ' = ' . $db->quote('com_cyonima'))
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
			foreach ([
				'core.manage', 'core.create', 'core.edit', 'core.edit.state',
				'core.delete', 'course.teach', 'grade.submission',
			] as $action) {
				$rules->mergeAction($action, [$teacherId => true]);
			}
		}

		if ($studentId) {
			$rules->mergeAction('course.enroll', [$studentId => true]);
		}

		// Any logged-in user can enroll by default.
		$registeredId = $this->getGroupId('Registered');
		$rules->mergeAction('course.enroll', [($registeredId ?: 2) => true]);

		$rulesJson = (string) $rules;

		$db->setQuery(
			$db->getQuery(true)
				->update($db->quoteName('#__assets'))
				->set($db->quoteName('rules') . ' = :rules')
				->where($db->quoteName('id') . ' = ' . $assetId)
				->bind(':rules', $rulesJson)
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
