<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\View\Learningpath;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Single learning path view.
 */
class HtmlView extends BaseHtmlView
{
	public $path;

	public $courses;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('learningpath.id', Factory::getApplication()->input->getInt('id'));

		$this->path    = $model->getPath();
		$this->courses = $model->getCourses();

		parent::display($tpl);
	}
}
