<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Course edit controller.
 */
class CourseController extends BaseCyonimaFormController
{
	protected $view_list  = 'courses';
	protected $view_item  = 'course';
	protected $model_name = 'Course';
}
