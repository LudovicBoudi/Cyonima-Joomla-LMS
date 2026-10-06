<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

use Cyonima\Component\Cyonima\Administrator\Helper\CyonimaHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

/**
 * Certificate template edit controller.
 */
class CertificatetemplateController extends BaseCyonimaFormController
{
	protected $view_list  = 'certificatetemplates';
	protected $view_item  = 'certificatetemplate';
	protected $model_name = 'Certificatetemplate';

	/**
	 * Handle the optional template image upload before saving.
	 *
	 * @return  void
	 */
	public function save()
	{
		$files = $this->input->files->get('jform', [], 'raw');

		if (!empty($files['image_file']['name'])) {
			$file   = $files['image_file'];
			$params = CyonimaHelper::getParams();
			$base   = trim($params->get('media_path', 'images/com_cyonima'), '/');
			$dir    = JPATH_ROOT . '/' . $base . '/templates';

			Folder::create($dir);

			$name   = time() . '_' . File::makeSafe($file['name']);
			$target = $dir . '/' . $name;

			if (File::upload($file['tmp_name'], $target)) {
				$jform = $this->input->post->get('jform', [], 'array');
				$jform['image'] = $base . '/templates/' . $name;
				$this->input->post->set('jform', $jform);
			}
		}

		parent::save();
	}
}
