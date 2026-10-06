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

/**
 * Certificate download controller.
 */
class CertificateController extends BaseController
{
	/**
	 * Stream a certificate file for download.
	 *
	 * @return  void
	 */
	public function download()
	{
		$app = Factory::getApplication();
		$id  = (int) $app->input->getInt('id');

		$model = $this->getModel('Certificates', 'Site');
		$cert  = $model->getItem($id);

		if (!$cert) {
			throw new \Exception(Text::_('COM_CYONIMA_ERROR_CERTIFICATE_NOT_FOUND'), 404);
		}

		$path = JPATH_ROOT . '/' . trim($cert->file_path, '/');

		if (!is_file($path)) {
			throw new \Exception(Text::_('COM_CYONIMA_ERROR_CERTIFICATE_FILE_MISSING'), 404);
		}

		$app->setHeader('Content-Type', 'image/png', true);
		$app->setHeader('Content-Disposition', 'attachment; filename="' . basename($path) . '"', true);
		$app->setHeader('Content-Length', (string) filesize($path), true);

		readfile($path);
		$app->close();
	}
}
