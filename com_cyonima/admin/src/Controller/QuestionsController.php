<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Questions list controller.
 */
class QuestionsController extends BaseCyonimaListController
{
	protected $view_item = 'question';

	protected function getRedirectToItemAppend(): string
	{
		$exam = $this->input->getInt('filter_exam');

		return $exam ? '&exam_id=' . $exam : '';
	}
}
