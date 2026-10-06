<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Question edit controller.
 */
class QuestionController extends BaseCyonimaFormController
{
	protected $view_list  = 'questions';
	protected $view_item  = 'question';
	protected $model_name = 'Question';
}
