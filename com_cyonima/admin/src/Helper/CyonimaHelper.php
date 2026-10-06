<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

/**
 * Cyonima component helper.
 */
abstract class CyonimaHelper
{
	/**
	 * The user group title for teachers.
	 *
	 * @var string
	 */
	public const GROUP_TEACHER = 'Teacher';

	/**
	 * The user group title for students.
	 *
	 * @var string
	 */
	public const GROUP_STUDENT = 'Student';

	/**
	 * Returns the component parameters.
	 *
	 * @return  Registry
	 */
	public static function getParams(): Registry
	{
		return Factory::getApplication()->bootComponent('com_cyonima')->getParams();
	}

	/**
	 * Returns the group id for a given group title.
	 *
	 * @param   string  $title  Group title.
	 *
	 * @return  integer
	 */
	public static function getGroupId(string $title): int
	{
		static $cache = [];

		if (isset($cache[$title])) {
			return $cache[$title];
		}

		$db = Factory::getContainer()->get('DatabaseDriver');

		$id = (int) $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__usergroups'))
				->where($db->quoteName('title') . ' = :title')
				->bind(':title', $title)
		)->loadResult();

		return $cache[$title] = $id;
	}

	/**
	 * Checks whether the given user belongs to a group.
	 *
	 * @param   string       $title  Group title.
	 * @param   User|null    $user   The user, defaults to current.
	 *
	 * @return  boolean
	 */
	public static function inGroup(string $title, ?User $user = null): bool
	{
		$user  = $user ?: Factory::getApplication()->getIdentity();
		$gid   = self::getGroupId($title);

		if (!$gid) {
			return false;
		}

		return \in_array($gid, $user->getAuthorisedGroups(), true);
	}

	/**
	 * Whether the user is a teacher.
	 *
	 * @param   User|null  $user  The user.
	 *
	 * @return  boolean
	 */
	public static function isTeacher(?User $user = null): bool
	{
		$user = $user ?: Factory::getApplication()->getIdentity();

		return $user->authorise('core.edit', 'com_cyonima') || self::inGroup(self::GROUP_TEACHER, $user);
	}

	/**
	 * Whether the user is a student.
	 *
	 * @param   User|null  $user  The user.
	 *
	 * @return  boolean
	 */
	public static function isStudent(?User $user = null): bool
	{
		$user = $user ?: Factory::getApplication()->getIdentity();

		return self::inGroup(self::GROUP_STUDENT, $user);
	}
}
