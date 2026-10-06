<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\View\Exam;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Exam taking view.
 */
class HtmlView extends BaseHtmlView
{
	public $exam;

	public $questions;

	public $attempts;

	public $canAttempt;

	public $attemptId;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('exam.id', Factory::getApplication()->input->getInt('id'));

		$this->exam       = $model->getExam();
		$this->questions  = $model->getQuestions();
		$this->attempts   = $model->getAttempts();
		$this->canAttempt = $model->canAttempt();
		$this->attemptId  = Factory::getApplication()->input->getInt('attempt');

		parent::display($tpl);
	}
}
