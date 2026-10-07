<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\View\Lessons;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Lessons list view.
 */
class HtmlView extends BaseHtmlView
{
	public $items;

	public $pagination;

	public $state;

	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_CYONIMA_LESSONS'), 'list');

		$courseId = (int) ($this->state->get('filter.course') ?: 0);

		if ($courseId) {
			ToolbarHelper::link(
				Route::_('index.php?option=com_cyonima&view=lesson&layout=edit&course_id=' . $courseId),
				'JTOOLBAR_NEW'
			);
		} else {
			ToolbarHelper::addNew('lessons.add');
		}
		ToolbarHelper::editList('lessons.edit');
		ToolbarHelper::publish('lessons.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('lessons.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		ToolbarHelper::deleteList('', 'lessons.delete');
		ToolbarHelper::preferences('com_cyonima');
	}
}
