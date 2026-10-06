<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\View\Lesson;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Single lesson player view.
 */
class HtmlView extends BaseHtmlView
{
	public $lesson;

	public $completed;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('lesson.id', Factory::getApplication()->input->getInt('id'));

		$this->lesson    = $model->getLesson();
		$this->completed = false;

		parent::display($tpl);
	}
}
