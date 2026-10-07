<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\View\Curriculum;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Curriculum view: sections and content of a course.
 */
class HtmlView extends BaseHtmlView
{
	public $course;

	public $sections;

	public $looseItems;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('curriculum.course_id', Factory::getApplication()->input->getInt('course_id'));

		$this->course      = $model->getCourse();
		$this->sections    = $model->getSections();
		$this->looseItems  = $model->getLooseItems();

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$title = Text::_('COM_CYONIMA_CURRICULUM');

		if (!empty($this->course->title)) {
			$title .= ' - ' . $this->course->title;
		}

		ToolbarHelper::title($title, 'category');

		if (!empty($this->course->id)) {
			ToolbarHelper::link(
				Route::_('index.php?option=com_cyonima&task=courses.edit&id=' . (int) $this->course->id, false),
				Text::_('JEDIT')
			);
		}

		ToolbarHelper::preferences('com_cyonima');
	}
}
