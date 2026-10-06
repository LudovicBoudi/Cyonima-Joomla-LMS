<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Learning path edit controller.
 */
class LearningpathController extends BaseCyonimaFormController
{
	protected $view_list  = 'learningpaths';
	protected $view_item  = 'learningpath';
	protected $model_name = 'Learningpath';
}
