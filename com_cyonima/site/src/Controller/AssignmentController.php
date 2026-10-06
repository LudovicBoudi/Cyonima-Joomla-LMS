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
 * Assignment controller (submission).
 */
class AssignmentController extends BaseController
{
	/**
	 * Submit an assignment.
	 *
	 * @return  void
	 */
	public function submit()
	{
		$this->checkToken();

		$app   = Factory::getApplication();
		$input = $app->input;
		$id    = (int) $input->getInt('id');

		$model = $this->getModel('Assignment', 'Site');
		$model->setState('assignment.id', $id);

		$redirect = Route::_('index.php?option=com_cyonima&view=assignment&id=' . $id);

		if (!$model->getAssignment()) {
			$app->enqueueMessage(Text::_('COM_CYONIMA_ERROR_ASSIGNMENT_NOT_FOUND'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_cyonima&view=courses'));

			return;
		}

		$content  = $input->get('content', '', 'raw');
		$fileName = '';

		$file = $input->files->get('file');

		if (!empty($file['name'])) {
			$fileName = $this->uploadFile($file);
		}

		if ($model->submit($content, $fileName)) {
			$app->enqueueMessage(Text::_('COM_CYONIMA_SUBMISSION_SUCCESS'), 'message');
		} else {
			$app->enqueueMessage($model->getError() ?: Text::_('COM_CYONIMA_SUBMISSION_ERROR'), 'error');
		}

		$this->setRedirect($redirect);
	}

	/**
	 * Upload a submission file and return its stored relative path.
	 *
	 * @param   array  $file  Uploaded file array.
	 *
	 * @return  string
	 */
	private function uploadFile(array $file): string
	{
		$app     = Factory::getApplication();
		$params  = $app->bootComponent('com_cyonima')->getParams();
		$base    = trim($params->get('media_path', 'images/com_cyonima'), '/');
		$sub     = 'submissions';
		$dir     = JPATH_ROOT . '/' . $base . '/' . $sub;
		$name    = \Joomla\Filesystem\File::makeSafe($file['name']);
		$target  = $dir . '/' . time() . '_' . $name;

		\Joomla\Filesystem\Folder::create($dir);

		if (!\Joomla\CMS\Filesystem\File::upload($file['tmp_name'], $target)) {
			return '';
		}

		return $base . '/' . $sub . '/' . basename($target);
	}
}
