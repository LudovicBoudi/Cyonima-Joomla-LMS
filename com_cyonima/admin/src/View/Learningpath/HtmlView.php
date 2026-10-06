<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\View\Learningpath;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Learning path edit view.
 */
class HtmlView extends BaseHtmlView
{
	public $item;

	public $courses;

	public $selectedCourses;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('learningpath.id', Factory::getApplication()->input->getInt('id'));

		$this->item = $model->getItem();

		$this->courses         = $model->getAllCourses();
		$this->selectedCourses = $model->getCourseIds();

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$isNew = empty($this->item->id);

		ToolbarHelper::title(Text::_('COM_CYONIMA_LEARNING_PATH') . ': ' . ($isNew ? Text::_('JNEW') : $this->item->title), 'folder');
		ToolbarHelper::apply('learningpath.apply');
		ToolbarHelper::save('learningpath.save');
		ToolbarHelper::cancel('learningpath.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
