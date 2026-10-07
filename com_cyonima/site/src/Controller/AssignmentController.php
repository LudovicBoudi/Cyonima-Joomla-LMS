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
 * Assignment controller (QCM attempts).
 */
class AssignmentController extends BaseController
{
	/**
	 * Start a new assignment attempt.
	 *
	 * @return  void
	 */
	public function start()
	{
		$this->checkToken();

		$app = Factory::getApplication();
		$id  = (int) $app->input->getInt('id');

		$model = $this->getModel('Assignment', 'Site');
		$model->setState('assignment.id', $id);

		$attemptId = $model->startAttempt();

		if ($attemptId) {
			$app->setUserState('com_cyonima.assignment.attempt', $attemptId);
			$app->redirect(Route::_('index.php?option=com_cyonima&view=assignment&id=' . $id . '&attempt=' . $attemptId));
		}

		$app->enqueueMessage($model->getError() ?: Text::_('COM_CYONIMA_EXAM_START_ERROR'), 'error');
		$this->setRedirect(Route::_('index.php?option=com_cyonima&view=assignment&id=' . $id));
	}

	/**
	 * Submit assignment answers and grade the attempt.
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

		$model = $this->getModel('Assignment', 'Site');
		$model->setState('assignment.id', $id);

		$answers = $input->get('answers', [], 'array');

		if ($model->submitAttempt($attemptId, $answers)) {
			$app->enqueueMessage(Text::_('COM_CYONIMA_EXAM_SUBMITTED'), 'message');
		} else {
			$app->enqueueMessage($model->getError() ?: Text::_('COM_CYONIMA_EXAM_SUBMIT_ERROR'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_cyonima&view=assignment&id=' . $id));
	}
}
