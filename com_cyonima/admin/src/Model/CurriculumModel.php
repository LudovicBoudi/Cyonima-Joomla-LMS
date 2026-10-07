<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Cyonima\Component\Cyonima\Administrator\Helper\ProgressHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;
use RuntimeException;

/**
 * Curriculum model: course sections and the lessons, assignments and exams they contain.
 */
class CurriculumModel extends BaseDatabaseModel
{
	/**
	 * Lesson types that can be created from the curriculum.
	 */
	private const ITEM_TYPES = ['content', 'video', 'pdf', 'link', 'assignment', 'exam'];

	/**
	 * @var  array|null  Lesson items indexed by section id (0 = no section).
	 */
	private $items;

	/**
	 * Returns the course being edited.
	 *
	 * @param   integer  $courseId  Course id, defaults to the view state.
	 *
	 * @return  object|null
	 */
	public function getCourse(int $courseId = 0)
	{
		$courseId = $courseId ?: $this->getCourseId();

		if (!$courseId) {
			return null;
		}

		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_courses'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $courseId, ParameterType::INTEGER)
		)->loadObject();
	}

	/**
	 * Returns the sections of the course, each with its ordered items.
	 *
	 * @return  array
	 */
	public function getSections(): array
	{
		$courseId = $this->getCourseId();

		if (!$courseId) {
			return [];
		}

		$db = $this->getDatabase();

		$sections = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_sections'))
				->where($db->quoteName('course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
				->order($db->quoteName('ordering') . ' ASC, ' . $db->quoteName('id') . ' ASC')
		)->loadObjectList() ?: [];

		$items = $this->getItems();

		foreach ($sections as $section) {
			$section->items = $items[(int) $section->id] ?? [];
		}

		return $sections;
	}

	/**
	 * Returns the items that are not assigned to any section.
	 *
	 * @return  array
	 */
	public function getLooseItems(): array
	{
		return $this->getItems()[0] ?? [];
	}

	/**
	 * Creates or updates a section.
	 *
	 * @param   array  $data  Section data (id, course_id, title).
	 *
	 * @return  integer|false  Section id on success.
	 */
	public function saveSection(array $data)
	{
		$courseId = (int) ($data['course_id'] ?? 0);
		$title    = trim((string) ($data['title'] ?? ''));
		$id       = (int) ($data['id'] ?? 0);

		if (!$this->getCourse($courseId)) {
			$this->setError(Text::_('COM_CYONIMA_ERROR_COURSE_NOT_FOUND'));

			return false;
		}

		if ($title === '') {
			$this->setError(Text::_('COM_CYONIMA_ERROR_TITLE_REQUIRED'));

			return false;
		}

		$db = $this->getDatabase();

		$section = $this->getMVCFactory()->createTable('Section');

		if ($id) {
			$section->load($id);

			if ((int) $section->course_id !== $courseId || !$section->id) {
				$this->setError(Text::_('COM_CYONIMA_ERROR_SECTION_NOT_FOUND'));

				return false;
			}
		} else {
			$section->course_id = $courseId;
			$section->ordering  = $this->nextOrder('#__cyonima_sections', 'course_id', $courseId);
		}

		$section->title = $title;

		if (!$section->save([])) {
			$this->setError($section->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));

			return false;
		}

		$this->normaliseSectionOrder($courseId);

		return (int) $section->id;
	}

	/**
	 * Removes a section. Its lessons are kept and move back to "no section".
	 *
	 * @param   integer  $id  Section id.
	 *
	 * @return  boolean
	 */
	public function deleteSection(int $id): bool
	{
		$db = $this->getDatabase();

		$section = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_sections'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		if (!$section) {
			$this->setError(Text::_('COM_CYONIMA_ERROR_SECTION_NOT_FOUND'));

			return false;
		}

		$db->transactionStart();

		try {
			$query = $db->getQuery(true)
				->update($db->quoteName('#__cyonima_lessons'))
				->set($db->quoteName('section_id') . ' = 0')
				->where($db->quoteName('section_id') . ' = :section')
				->bind(':section', $id, ParameterType::INTEGER);
			$db->setQuery($query)->execute();

			if (!$this->getMVCFactory()->createTable('Section')->delete($id)) {
				throw new RuntimeException($this->getError() ?: Text::_('JLIB_APPLICATION_ERROR_DELETE_FAILED'));
			}

			$db->transactionCommit();
		} catch (\Throwable $e) {
			$db->transactionRollback();
			$this->setError($e->getMessage());

			return false;
		}

		$this->normaliseSectionOrder((int) $section->course_id);

		return true;
	}

	/**
	 * Moves a section up or down within its course.
	 *
	 * @param   integer  $id     Section id.
	 * @param   integer  $delta  1 to move down, -1 to move up.
	 *
	 * @return  boolean
	 */
	public function moveSection(int $id, int $delta): bool
	{
		$db       = $this->getDatabase();
		$courseId = $this->getCourseId($id);
		$rows     = $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__cyonima_sections'))
				->where($db->quoteName('course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
				->order($db->quoteName('ordering') . ' ASC, ' . $db->quoteName('id') . ' ASC')
		)->loadColumn();

		$ids = array_map('intval', $rows ?: []);

		if (!$this->swap($ids, $id, $delta)) {
			return true;
		}

		$this->writeOrder('#__cyonima_sections', $ids);

		return true;
	}

	/**
	 * Creates a lesson, assignment or exam inside a section.
	 *
	 * @param   array  $data  Item data (course_id, section_id, title, type).
	 *
	 * @return  integer|false  Lesson id on success.
	 */
	public function addItem(array $data)
	{
		$courseId  = (int) ($data['course_id'] ?? 0);
		$sectionId = (int) ($data['section_id'] ?? 0);
		$title     = trim((string) ($data['title'] ?? ''));
		$type      = (string) ($data['type'] ?? 'content');

		if (!$this->getCourse($courseId)) {
			$this->setError(Text::_('COM_CYONIMA_ERROR_COURSE_NOT_FOUND'));

			return false;
		}

		if ($title === '') {
			$this->setError(Text::_('COM_CYONIMA_ERROR_TITLE_REQUIRED'));

			return false;
		}

		if (!\in_array($type, self::ITEM_TYPES, true)) {
			$this->setError(Text::_('COM_CYONIMA_ERROR_INVALID_TYPE'));

			return false;
		}

		if ($sectionId && !$this->getSection($sectionId, $courseId)) {
			$this->setError(Text::_('COM_CYONIMA_ERROR_SECTION_NOT_FOUND'));

			return false;
		}

		$db = $this->getDatabase();

		$db->transactionStart();

		try {
			$lesson = $this->getMVCFactory()->createTable('Lesson');

			if (!$lesson->save(
				[
					'course_id'   => $courseId,
					'section_id'  => $sectionId,
					'title'       => $title,
					'type'        => $type,
					'description' => '',
					'content'     => '',
					'published'   => 1,
					'access'      => 1,
					'ordering'    => $this->nextOrder('#__cyonima_lessons', 'section_id', $sectionId, 'course_id = ' . $courseId),
				]
			)) {
				throw new RuntimeException($lesson->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
			}

			if ($type === 'assignment') {
				$this->createLinkedRow('Assignment', $lesson->id, $courseId, $title);
			} elseif ($type === 'exam') {
				$this->createLinkedRow('Exam', $lesson->id, $courseId, $title);
			}

			$db->transactionCommit();
		} catch (\Throwable $e) {
			$db->transactionRollback();
			$this->setError($e->getMessage());

			return false;
		}

		ProgressHelper::recalculateCourse($courseId);

		return (int) $lesson->id;
	}

	/**
	 * Removes a lesson with its linked assignment or exam, questions, attempts and progress.
	 *
	 * @param   integer $id  Lesson id.
	 *
	 * @return  boolean
	 */
	public function deleteItem(int $id): bool
	{
		$db = $this->getDatabase();

		$lesson = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_lessons'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		if (!$lesson) {
			$this->setError(Text::_('COM_CYONIMA_ERROR_LESSON_NOT_FOUND'));

			return false;
		}

		$assignmentIds = array_map('intval', $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__cyonima_assignments'))
				->where($db->quoteName('lesson_id') . ' = :lesson')
				->bind(':lesson', $id, ParameterType::INTEGER)
		)->loadColumn() ?: []);

		$examIds = array_map('intval', $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__cyonima_exams'))
				->where($db->quoteName('lesson_id') . ' = :lesson')
				->bind(':lesson', $id, ParameterType::INTEGER)
		)->loadColumn() ?: []);

		$db->transactionStart();

		try {
			foreach ([['#__cyonima_submissions', 'assignment_id'], ['#__cyonima_questions', 'assignment_id'], ['#__cyonima_exam_attempts', 'assignment_id']] as $target) {
				if ($assignmentIds) {
					$this->deleteWhere($target[0], $target[1], $assignmentIds);
				}
			}

			foreach ([['#__cyonima_questions', 'exam_id'], ['#__cyonima_exam_attempts', 'exam_id']] as $target) {
				if ($examIds) {
					$this->deleteWhere($target[0], $target[1], $examIds);
				}
			}

			if ($assignmentIds) {
				$this->deleteWhere('#__cyonima_assignments', 'id', $assignmentIds);
			}

			if ($examIds) {
				$this->deleteWhere('#__cyonima_exams', 'id', $examIds);
			}

			$this->deleteWhere('#__cyonima_lesson_progress', 'lesson_id', [$id]);

			if (!$this->getMVCFactory()->createTable('Lesson')->delete($id)) {
				throw new RuntimeException(Text::_('JLIB_APPLICATION_ERROR_DELETE_FAILED'));
			}

			$db->transactionCommit();
		} catch (\Throwable $e) {
			$db->transactionRollback();
			$this->setError($e->getMessage());

			return false;
		}

		$this->normaliseItemOrder((int) $lesson->course_id, (int) $lesson->section_id);
		ProgressHelper::recalculateCourse((int) $lesson->course_id);

		return true;
	}

	/**
	 * Moves a lesson up or down within its section.
	 *
	 * @param   integer  $id     Lesson id.
	 * @param   integer  $delta  1 to move down, -1 to move up.
	 *
	 * @return  boolean
	 */
	public function moveItem(int $id, int $delta): bool
	{
		$db = $this->getDatabase();

		$lesson = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_lessons'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		if (!$lesson) {
			$this->setError(Text::_('COM_CYONIMA_ERROR_LESSON_NOT_FOUND'));

			return false;
		}

		$courseId  = (int) $lesson->course_id;
		$sectionId = (int) $lesson->section_id;
		$rows      = $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__cyonima_lessons'))
				->where($db->quoteName('course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
				->where($db->quoteName('section_id') . ' = :section')
				->bind(':section', $sectionId, ParameterType::INTEGER)
				->order($db->quoteName('ordering') . ' ASC, ' . $db->quoteName('id') . ' ASC')
		)->loadColumn();

		$ids = array_map('intval', $rows ?: []);

		if (!$this->swap($ids, $id, $delta)) {
			return true;
		}

		$this->writeOrder('#__cyonima_lessons', $ids);

		return true;
	}

	/**
	 * Returns the lesson items of the course indexed by section id (0 = no section).
	 *
	 * @return  array
	 */
	private function getItems(): array
	{
		if ($this->items !== null) {
			return $this->items;
		}

		$this->items = [];

		$courseId = $this->getCourseId();

		if (!$courseId) {
			return $this->items;
		}

		$db = $this->getDatabase();

		$lessons = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_lessons'))
				->where($db->quoteName('course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
				->order($db->quoteName('ordering') . ' ASC, ' . $db->quoteName('id') . ' ASC')
		)->loadObjectList() ?: [];

		$assignments = $db->setQuery(
			$db->getQuery(true)
				->select([$db->quoteName('id'), $db->quoteName('lesson_id'), $db->quoteName('published')])
				->from($db->quoteName('#__cyonima_assignments'))
				->where($db->quoteName('course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
		)->loadObjectList() ?: [];

		$exams = $db->setQuery(
			$db->getQuery(true)
				->select([$db->quoteName('id'), $db->quoteName('lesson_id'), $db->quoteName('published')])
				->from($db->quoteName('#__cyonima_exams'))
				->where($db->quoteName('course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
		)->loadObjectList() ?: [];

		$links = [];
		$counts = ['assignment' => [], 'exam' => []];

		foreach ($assignments as $row) {
			$links[(int) $row->lesson_id] = ['type' => 'assignment', 'id' => (int) $row->id, 'published' => (int) $row->published];
		}

		foreach ($exams as $row) {
			$links[(int) $row->lesson_id] = ['type' => 'exam', 'id' => (int) $row->id, 'published' => (int) $row->published];
		}

		$counts['assignment'] = $this->countQuestions('assignment_id', array_column($assignments, 'id'));
		$counts['exam']       = $this->countQuestions('exam_id', array_column($exams, 'id'));

		foreach ($lessons as $lesson) {
			$lessonId = (int) $lesson->id;
			$link     = $links[$lessonId] ?? null;

			$item = (object) [
				'id'                => $lessonId,
				'section_id'        => (int) $lesson->section_id,
				'title'             => (string) $lesson->title,
				'alias'             => (string) $lesson->alias,
				'type'              => (string) $lesson->type,
				'published'         => (int) $lesson->published,
				'ordering'          => (int) $lesson->ordering,
				'linked_id'         => $link['id'] ?? 0,
				'linked_published'  => $link['published'] ?? 0,
				'question_count'    => $link ? ($counts[$link['type']][$link['id']] ?? 0) : 0,
			];

			$this->items[(int) $lesson->section_id][] = $item;
		}

		return $this->items;
	}

	/**
	 * Counts questions grouped by parent id.
	 *
	 * @param   string  $column  Parent column name.
	 * @param   array   $ids     Parent ids.
	 *
	 * @return  array  Count per parent id.
	 */
	private function countQuestions(string $column, array $ids): array
	{
		$ids = array_map('intval', $ids);

		if (!$ids) {
			return [];
		}

		$db = $this->getDatabase();

		$rows = $db->setQuery(
			$db->getQuery(true)
				->select([$db->quoteName($column, 'parent_id'), 'COUNT(1) AS ' . $db->quoteName('total')])
				->from($db->quoteName('#__cyonima_questions'))
				->where($db->quoteName($column) . ' IN (' . implode(',', $ids) . ')')
				->group($db->quoteName($column))
		)->loadAssocList() ?: [];

		$counts = [];

		foreach ($rows as $row) {
			$counts[(int) $row['parent_id']] = (int) $row['total'];
		}

		return $counts;
	}

	/**
	 * Creates the assignment or exam row linked to a lesson.
	 *
	 * @param   string  $name      MVC factory name (Assignment|Exam).
	 * @param   integer $lessonId  Lesson id.
	 * @param   integer $courseId  Course id.
	 * @param   string  $title     Item title.
	 *
	 * @return  void
	 * @throws  RuntimeException  When the row could not be saved.
	 */
	private function createLinkedRow(string $name, int $lessonId, int $courseId, string $title): void
	{
		$table = $this->getMVCFactory()->createTable($name);

		$data = [
			'lesson_id' => $lessonId,
			'course_id' => $courseId,
			'title'     => $title,
			'published' => 1,
		];

		if ($name === 'Assignment') {
			$data += [
				'description'      => '',
				'max_score'        => 100,
				'coefficient'      => 1,
				'attempts_allowed' => 1,
			];
		} else {
			$data += [
				'description'      => '',
				'time_limit'       => 0,
				'pass_mark'        => 50,
				'attempts_allowed' => 1,
				'shuffle'          => 0,
				'coefficient'      => 1,
			];
		}

		if (!$table->save($data)) {
			throw new RuntimeException($table->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
		}
	}

	/**
	 * Returns a section when it belongs to the given course.
	 *
	 * @param   integer  $id        Section id.
	 * @param   integer  $courseId  Course id.
	 *
	 * @return  object|null
	 */
	private function getSection(int $id, int $courseId = 0)
	{
		$db = $this->getDatabase();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_sections'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
				->where($db->quoteName('course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
		)->loadObject();
	}

	/**
	 * Returns the next ordering value in a scope.
	 *
	 * @param   string       $table       Table name.
	 * @param   string       $scopeField  Scope column.
	 * @param   integer      $scopeValue  Scope value.
	 * @param   string|null  $extraWhere  Extra raw condition (integers only).
	 *
	 * @return  integer
	 */
	private function nextOrder(string $table, string $scopeField, int $scopeValue, ?string $extraWhere = null): int
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('COALESCE(MAX(' . $db->quoteName('ordering') . '), 0) + 1')
			->from($db->quoteName($table))
			->where($db->quoteName($scopeField) . ' = :scope')
			->bind(':scope', $scopeValue, ParameterType::INTEGER);

		if ($extraWhere) {
			$query->where($extraWhere);
		}

		return (int) $db->setQuery($query)->loadResult();
	}

	/**
	 * Rewrites the ordering column as 1..n for the given ids.
	 *
	 * @param   string  $table  Table name.
	 * @param   array   $ids    Ordered ids.
	 *
	 * @return  void
	 */
	private function writeOrder(string $table, array $ids): void
	{
		$db = $this->getDatabase();

		foreach ($ids as $position => $id) {
			$id = (int) $id;

			$db->setQuery(
				$db->getQuery(true)
					->update($db->quoteName($table))
					->set($db->quoteName('ordering') . ' = ' . ($position + 1))
					->where($db->quoteName('id') . ' = ' . $id)
			)->execute();
		}
	}

	/**
	 * Swaps an id with its neighbour inside an ordered list.
	 *
	 * @param   array    $ids   Ids in display order (by reference).
	 * @param   integer  $id    Id to move.
	 * @param   integer  $delta 1 to move down, -1 to move up.
	 *
	 * @return  boolean  False when the move is not possible.
	 */
	private function swap(array &$ids, int $id, int $delta): bool
	{
		$index = array_search($id, $ids, true);

		if ($index === false) {
			return false;
		}

		$next = $index + ($delta < 0 ? -1 : 1);

		if ($next < 0 || $next >= \count($ids)) {
			return false;
		}

		$tmp          = $ids[$index];
		$ids[$index]  = $ids[$next];
		$ids[$next]   = $tmp;

		return true;
	}

	/**
	 * Renumbers the sections of a course.
	 *
	 * @param   integer  $courseId  Course id.
	 *
	 * @return  void
	 */
	private function normaliseSectionOrder(int $courseId): void
	{
		$db = $this->getDatabase();

		$ids = $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__cyonima_sections'))
				->where($db->quoteName('course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
				->order($db->quoteName('ordering') . ' ASC, ' . $db->quoteName('id') . ' ASC')
		)->loadColumn();

		$this->writeOrder('#__cyonima_sections', array_map('intval', $ids ?: []));
	}

	/**
	 * Renumbers the lessons of a section.
	 *
	 * @param   integer  $courseId   Course id.
	 * @param   integer  $sectionId  Section id (0 for no section).
	 *
	 * @return  void
	 */
	private function normaliseItemOrder(int $courseId, int $sectionId): void
	{
		$db = $this->getDatabase();

		$ids = $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__cyonima_lessons'))
				->where($db->quoteName('course_id') . ' = :course')
				->bind(':course', $courseId, ParameterType::INTEGER)
				->where($db->quoteName('section_id') . ' = :section')
				->bind(':section', $sectionId, ParameterType::INTEGER)
				->order($db->quoteName('ordering') . ' ASC, ' . $db->quoteName('id') . ' ASC')
		)->loadColumn();

		$this->writeOrder('#__cyonima_lessons', array_map('intval', $ids ?: []));
	}

	/**
	 * Deletes rows matching a list of integer ids.
	 *
	 * @param   string  $table   Table name.
	 * @param   string  $column  Column name.
	 * @param   array   $ids     Ids to delete.
	 *
	 * @return  void
	 */
	private function deleteWhere(string $table, string $column, array $ids): void
	{
		$db  = $this->getDatabase();
		$ids = array_map('intval', $ids);

		if (!$ids) {
			return;
		}

		$db->setQuery(
			$db->getQuery(true)
				->delete($db->quoteName($table))
				->where($db->quoteName($column) . ' IN (' . implode(',', $ids) . ')')
		)->execute();
	}

	/**
	 * Returns the course id of the view, falling back to the owner of a row.
	 *
	 * @param   integer  $rowId  Section id used to resolve the course.
	 *
	 * @return  integer
	 */
	private function getCourseId(int $rowId = 0): int
	{
		$courseId = (int) $this->getState('curriculum.course_id');

		if (!$courseId && $rowId) {
			$db       = $this->getDatabase();
			$courseId = (int) $db->setQuery(
				$db->getQuery(true)
					->select($db->quoteName('course_id'))
					->from($db->quoteName('#__cyonima_sections'))
					->where($db->quoteName('id') . ' = :id')
					->bind(':id', $rowId, ParameterType::INTEGER)
			)->loadResult();
		}

		return $courseId;
	}
}
