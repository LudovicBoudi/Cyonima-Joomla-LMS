<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Exam controller (start and submit attempts).
 */
class ExamController extends BaseController
{
	/**
	 * Start a new exam attempt.
	 *
	 * @return  void
	 */
	public function start()
	{
		$this->checkToken();

		$app = Factory::getApplication();
		$id  = (int) $app->input->getInt('id');

		$model = $this->getModel('Exam', 'Site');
		$model->setState('exam.id', $id);

		$attemptId = $model->startAttempt();

		if ($attemptId) {
			$app->setUserState('com_cyonima.exam.attempt', $attemptId);
			$app->redirect(Route::_('index.php?option=com_cyonima&view=exam&id=' . $id . '&attempt=' . $attemptId));
		}

		$app->enqueueMessage(Text::_('COM_CYONIMA_EXAM_START_ERROR'), 'error');
		$this->setRedirect(Route::_('index.php?option=com_cyonima&view=course&id=' . $id));
	}

	/**
	 * Submit exam answers and grade the attempt.
	 *
	 * @return  void
	 */
	public function submit()
	{
		$this->checkToken();

		$app       = Factory::getApplication();
		$input     = $app->input;
		$id        = (int) $input->getInt('id');
		$attemptId = (int) $input->getInt('attempt');

		$model = $this->getModel('Exam', 'Site');
		$model->setState('exam.id', $id);

		$answers = $input->get('answers', [], 'array');

		if ($model->submitAttempt($attemptId, $answers)) {
			$app->enqueueMessage(Text::_('COM_CYONIMA_EXAM_SUBMITTED'), 'message');
		} else {
			$app->enqueueMessage($model->getError() ?: Text::_('COM_CYONIMA_EXAM_SUBMIT_ERROR'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_cyonima&view=exam&id=' . $id));
	}
}
