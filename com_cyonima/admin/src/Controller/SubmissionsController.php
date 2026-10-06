<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Submissions list controller (grading).
 */
class SubmissionsController extends BaseCyonimaListController
{
	protected $view_item = '';

	/**
	 * Grade a submission.
	 *
	 * @return  void
	 */
	public function grade()
	{
		$this->checkToken();

		$id      = $this->input->getInt('id');
		$score   = $this->input->get('score', 0, 'float');
		$feedback = $this->input->get('feedback', '', 'raw');

		$model = $this->getModel('Submission', 'Administrator');

		if ($model->grade($id, $score, $feedback)) {
			$this->setMessage(Text::_('COM_CYONIMA_SUBMISSION_GRADED'));
		} else {
			$this->setMessage($model->getError(), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_cyonima&view=submissions', false));
	}
}
