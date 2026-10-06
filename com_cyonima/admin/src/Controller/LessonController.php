<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Lesson edit controller.
 */
class LessonController extends BaseCyonimaFormController
{
	protected $view_list  = 'lessons';
	protected $view_item  = 'lesson';
	protected $model_name = 'Lesson';
}
