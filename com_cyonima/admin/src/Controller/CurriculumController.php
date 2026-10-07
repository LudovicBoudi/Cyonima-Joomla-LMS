<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Curriculum controller: section and curriculum item tasks.
 */
class CurriculumController extends BaseController
{
	/**
	 * Saves a section (create or update).
	 *
	 * @return  void
	 */
	public function sectionSave()
	{
		$this->checkToken();

		$courseId = $this->input->getInt('course_id');
		$model    = $this->getCurriculumModel($courseId);
		$data     = $this->input->post->get('jform', [], 'array');
		$data['course_id'] = $courseId;

		$id = $model->saveSection($data);

		if (!$id) {
			$this->setMessage($model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'), 'error');
		} else {
			$this->setMessage(Text::_('COM_CYONIMA_SECTION_SAVED'));
		}

		$this->redirectTo($courseId);
	}

	/**
	 * Removes a section, keeping its lessons.
	 *
	 * @return  void
	 */
	public function sectionDelete()
	{
		$this->checkToken();

		$courseId = $this->input->getInt('course_id');
		$model    = $this->getCurriculumModel($courseId);

		if (!$model->deleteSection($this->input->getInt('id'))) {
			$this->setMessage($model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_DELETE_FAILED'), 'error');
		} else {
			$this->setMessage(Text::_('COM_CYONIMA_SECTION_DELETED'));
		}

		$this->redirectTo($courseId);
	}

	/**
	 * Moves a section up or down.
	 *
	 * @return  void
	 */
	public function sectionMove()
	{
		$this->checkToken();

		$courseId = $this->input->getInt('course_id');
		$model    = $this->getCurriculumModel($courseId);

		if (!$model->moveSection($this->input->getInt('id'), $this->input->getInt('delta') ?: 1)) {
			$this->setMessage($model->getError(), 'error');
		}

		$this->redirectTo($courseId);
	}

	/**
	 * Adds a lesson, assignment or exam to a section.
	 *
	 * @return  void
	 */
	public function itemAdd()
	{
		$this->checkToken();

		$courseId = $this->input->getInt('course_id');
		$model    = $this->getCurriculumModel($courseId);
		$data     = $this->input->post->get('jform', [], 'array');
		$data['course_id']  = $courseId;
		$data['section_id'] = $this->input->getInt('section_id');

		$id = $model->addItem($data);

		if (!$id) {
			$this->setMessage($model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'), 'error');
		} else {
			$this->setMessage(Text::_('COM_CYONIMA_ITEM_ADDED'));
		}

		$this->redirectTo($courseId);
	}

	/**
	 * Removes a curriculum item with its linked assignment or exam.
	 *
	 * @return  void
	 */
	public function itemDelete()
	{
		$this->checkToken();

		$courseId = $this->input->getInt('course_id');
		$model    = $this->getCurriculumModel($courseId);

		if (!$model->deleteItem($this->input->getInt('id'))) {
			$this->setMessage($model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_DELETE_FAILED'), 'error');
		} else {
			$this->setMessage(Text::_('COM_CYONIMA_ITEM_DELETED'));
		}

		$this->redirectTo($courseId);
	}

	/**
	 * Moves an item up or down within its section.
	 *
	 * @return  void
	 */
	public function itemMove()
	{
		$this->checkToken();

		$courseId = $this->input->getInt('course_id');
		$model    = $this->getCurriculumModel($courseId);

		if (!$model->moveItem($this->input->getInt('id'), $this->input->getInt('delta') ?: 1)) {
			$this->setMessage($model->getError(), 'error');
		}

		$this->redirectTo($courseId);
	}

	/**
	 * Returns the curriculum model with the course id set in its state.
	 *
	 * @param   integer  $courseId  Course id.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 */
	protected function getCurriculumModel(int $courseId)
	{
		$model = $this->getModel('Curriculum', 'Administrator');
		$model->setState('curriculum.course_id', $courseId);

		return $model;
	}

	/**
	 * Redirects back to the curriculum of the course.
	 *
	 * @param   integer  $courseId  Course id.
	 *
	 * @return  void
	 */
	protected function redirectTo(int $courseId): void
	{
		$this->setRedirect(
			Route::_('index.php?option=com_cyonima&view=curriculum&course_id=' . $courseId, false)
		);
	}
}
