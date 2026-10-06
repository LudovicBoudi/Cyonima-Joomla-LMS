<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\View\Monitor;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Monitor view: per-course student progress and results.
 */
class HtmlView extends BaseHtmlView
{
	public $course;

	public $students;

	public $results;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('monitor.course_id', Factory::getApplication()->input->getInt('course_id'));

		$this->course   = $model->getCourse();
		$this->students = $model->getStudents();
		$this->results  = $model->getResults();

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$title = Text::_('COM_CYONIMA_MONITOR');

		if (!empty($this->course->title)) {
			$title .= ' - ' . $this->course->title;
		}

		ToolbarHelper::title($title, 'chart');
		ToolbarHelper::preferences('com_cyonima');
	}
}
