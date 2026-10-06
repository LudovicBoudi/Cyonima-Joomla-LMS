<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * Lessons list controller.
 */
class LessonsController extends BaseCyonimaListController
{
	protected $view_item = 'lesson';

	protected function getRedirectToItemAppend(): string
	{
		$course = $this->input->getInt('filter_course');

		return $course ? '&course_id=' . $course : '';
	}
}
