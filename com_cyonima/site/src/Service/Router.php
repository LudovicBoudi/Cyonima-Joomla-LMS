<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;

/**
 * Routing class for com_cyonima.
 */
class Router extends RouterView
{
	/**
	 * @param   CMSApplication     $app   Application.
	 * @param   AbstractMenu       $menu  Menu.
	 */
	public function __construct(CMSApplication $app, AbstractMenu $menu)
	{
		$courses = new RouterViewConfiguration('courses');
		$courses->setKey('id');
		$this->registerView($courses);

		$course = new RouterViewConfiguration('course');
		$course->setKey('id')->setNestable();
		$this->registerView($course);

		$lesson = new RouterViewConfiguration('lesson');
		$lesson->setKey('id');
		$this->registerView($lesson);

		$learningpaths = new RouterViewConfiguration('learningpaths');
		$learningpaths->setKey('id');
		$this->registerView($learningpaths);

		$learningpath = new RouterViewConfiguration('learningpath');
		$learningpath->setKey('id');
		$this->registerView($learningpath);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	/**
	 * Segment for a course id.
	 *
	 * @param   integer  $id     Course id.
	 * @param   array    $query  Query.
	 *
	 * @return  array
	 */
	public function getCourseSegment($id, $query): array
	{
		return [(int) $id => $this->getCourseAlias((int) $id)];
	}

	/**
	 * Resolve a course segment to its id.
	 *
	 * @param   string  $segment  Segment.
	 * @param   array   $query    Query.
	 *
	 * @return  integer
	 */
	public function getCourseId($segment, $query): int
	{
		return (int) $this->findByAlias('#__cyonima_courses', $segment, 'id');
	}

	/**
	 * Segment for a lesson id.
	 *
	 * @param   integer  $id     Lesson id.
	 * @param   array    $query  Query.
	 *
	 * @return  array
	 */
	public function getLessonSegment($id, $query): array
	{
		return [(int) $id => $this->getLessonAlias((int) $id)];
	}

	/**
	 * Resolve a lesson segment to its id.
	 *
	 * @param   string  $segment  Segment.
	 * @param   array   $query    Query.
	 *
	 * @return  integer
	 */
	public function getLessonId($segment, $query): int
	{
		return (int) $this->findByAlias('#__cyonima_lessons', $segment, 'id');
	}

	/**
	 * Segment for a learning path id.
	 *
	 * @param   integer  $id     Learning path id.
	 * @param   array    $query  Query.
	 *
	 * @return  array
	 */
	public function getLearningpathSegment($id, $query): array
	{
		return [(int) $id => $this->getLearningpathAlias((int) $id)];
	}

	/**
	 * Resolve a learning path segment to its id.
	 *
	 * @param   string  $segment  Segment.
	 * @param   array   $query    Query.
	 *
	 * @return  integer
	 */
	public function getLearningpathId($segment, $query): int
	{
		return (int) $this->findByAlias('#__cyonima_learning_paths', $segment, 'id');
	}

	/**
	 * @param   integer  $id  Course id.
	 *
	 * @return  string
	 */
	private function getCourseAlias(int $id): string
	{
		return $this->getAlias('#__cyonima_courses', $id);
	}

	/**
	 * @param   integer  $id  Lesson id.
	 *
	 * @return  string
	 */
	private function getLessonAlias(int $id): string
	{
		return $this->getAlias('#__cyonima_lessons', $id);
	}

	/**
	 * @param   integer  $id  Learning path id.
	 *
	 * @return  string
	 */
	private function getLearningpathAlias(int $id): string
	{
		return $this->getAlias('#__cyonima_learning_paths', $id);
	}

	/**
	 * @param   string   $table  Table name.
	 * @param   integer  $id     Row id.
	 *
	 * @return  string
	 */
	private function getAlias(string $table, int $id): string
	{
		$db = $this->getDatabase();

		$alias = (string) $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('alias'))
				->from($db->quoteName($table))
				->where($db->quoteName('id') . ' = ' . $id)
		)->loadResult();

		return $alias ?: $id;
	}

	/**
	 * @param   string  $table   Table name.
	 * @param   string  $alias   Alias.
	 * @param   string  $column  Column to return.
	 *
	 * @return  mixed
	 */
	private function findByAlias(string $table, string $alias, string $column)
	{
		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName($column))
				->from($db->quoteName($table))
				->where($db->quoteName('alias') . ' = ' . $db->quote($alias))
		)->loadResult();
	}

	/**
	 * @return  DatabaseInterface
	 */
	private function getDatabase(): DatabaseInterface
	{
		return \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
	}
}
