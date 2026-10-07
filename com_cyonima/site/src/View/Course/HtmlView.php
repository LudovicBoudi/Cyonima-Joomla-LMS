<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\View\Course;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Single course view.
 */
class HtmlView extends BaseHtmlView
{
	public $course;

	public $lessons;

	public $groups;

	public $enrollment;

	public $completed;

	public $isTeacher;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('course.id', Factory::getApplication()->input->getInt('id'));

		$this->course     = $model->getCourse();
		$this->lessons    = $model->getLessons();
		$this->groups     = $model->getLessonGroups();
		$this->enrollment = $model->getEnrollment();
		$this->completed  = $model->getCompletedLessons();
		$this->isTeacher  = \Cyonima\Component\Cyonima\Administrator\Helper\CyonimaHelper::isTeacher();

		parent::display($tpl);
	}
}
