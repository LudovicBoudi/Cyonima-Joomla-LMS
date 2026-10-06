<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\View\Assignment;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Assignment view.
 */
class HtmlView extends BaseHtmlView
{
	public $assignment;

	public $submission;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('assignment.id', Factory::getApplication()->input->getInt('id'));

		$this->assignment = $model->getAssignment();
		$this->submission = $model->getSubmission();

		parent::display($tpl);
	}
}
