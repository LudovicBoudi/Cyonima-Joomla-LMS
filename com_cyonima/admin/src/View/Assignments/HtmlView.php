<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\View\Assignments;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Assignments list view.
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
		ToolbarHelper::title(Text::_('COM_CYONIMA_ASSIGNMENTS'), 'book');
		ToolbarHelper::addNew('assignments.add');
		ToolbarHelper::editList('assignments.edit');
		ToolbarHelper::publish('assignments.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('assignments.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		ToolbarHelper::deleteList('', 'assignments.delete');
		ToolbarHelper::preferences('com_cyonima');
	}
}
