<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Database\DatabaseInterface;

/**
 * Issued certificates list controller.
 */
class CertificatesController extends AdminController
{
	/**
	 * Download a certificate file.
	 *
	 * @return  void
	 */
	public function download()
	{
		$app = Factory::getApplication();
		$id  = $app->input->getInt('id');

		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$cert = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_certificates'))
				->where($db->quoteName('id') . ' = ' . $id)
		)->loadObject();

		if (!$cert || !is_file(JPATH_ROOT . '/' . trim($cert->file_path, '/'))) {
			throw new \Exception('Certificate file not found', 404);
		}

		$path = JPATH_ROOT . '/' . trim($cert->file_path, '/');

		$app->setHeader('Content-Type', 'image/png', true)
			->setHeader('Content-Disposition', 'attachment; filename="' . basename($path) . '"', true)
			->setHeader('Content-Length', (string) filesize($path), true)
			->sendHeaders();

		readfile($path);
		$app->close();
	}
}
