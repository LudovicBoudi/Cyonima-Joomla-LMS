<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Exam edit controller.
 */
class ExamController extends BaseCyonimaFormController
{
	protected $view_list  = 'exams';
	protected $view_item  = 'exam';
	protected $model_name = 'Exam';
}
