<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\View\Courses;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Courses list view.
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
		ToolbarHelper::title(Text::_('COM_CYONIMA_COURSES'), 'book');
		ToolbarHelper::addNew('courses.add');
		ToolbarHelper::editList('courses.edit');
		ToolbarHelper::publish('courses.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('courses.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		ToolbarHelper::deleteList('', 'courses.delete');
		ToolbarHelper::preferences('com_cyonima');
	}
}
