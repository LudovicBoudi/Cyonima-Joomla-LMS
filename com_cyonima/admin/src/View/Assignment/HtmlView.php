<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\View\Assignment;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Assignment edit view.
 */
class HtmlView extends BaseHtmlView
{
	public $item;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('assignment.id', Factory::getApplication()->input->getInt('id'));

		$this->item = $model->getItem();

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$isNew = empty($this->item->id);

		ToolbarHelper::title(Text::_('COM_CYONIMA_ASSIGNMENT') . ': ' . ($isNew ? Text::_('JNEW') : $this->item->title), 'book');
		ToolbarHelper::apply('assignment.apply');
		ToolbarHelper::save('assignment.save');
		ToolbarHelper::cancel('assignment.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
