<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Assignment edit controller.
 */
class AssignmentController extends BaseCyonimaFormController
{
	protected $view_list  = 'assignments';
	protected $view_item  = 'assignment';
	protected $model_name = 'Assignment';
}
