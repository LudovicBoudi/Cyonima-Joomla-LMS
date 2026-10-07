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
 * Assignment taking view.
 */
class HtmlView extends BaseHtmlView
{
	public $assignment;

	public $questions;

	public $attempts;

	public $canAttempt;

	public $attemptId;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('assignment.id', Factory::getApplication()->input->getInt('id'));

		$this->assignment = $model->getQuiz();
		$this->questions  = $model->getQuestions();
		$this->attempts   = $model->getAttempts();
		$this->canAttempt = $model->canAttempt();
		$this->attemptId  = Factory::getApplication()->input->getInt('attempt');

		parent::display($tpl);
	}
}
