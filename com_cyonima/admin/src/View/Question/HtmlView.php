<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\View\Question;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Question edit view.
 */
class HtmlView extends BaseHtmlView
{
	public $item;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('question.id', Factory::getApplication()->input->getInt('id'));

		$this->item = $model->getItem();

		if ($this->item) {
			$options = json_decode($this->item->options ?: '[]', true);

			$this->item->options      = \is_array($options) ? $options : [];
			$this->item->options_text = implode("\n", $this->item->options);

			$answer = json_decode($this->item->answer, true);

			$this->item->answer_text = \is_array($answer) ? implode(',', $answer) : $this->item->answer;
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$isNew = empty($this->item->id);

		ToolbarHelper::title(Text::_('COM_CYONIMA_QUESTION') . ': ' . ($isNew ? Text::_('JNEW') : $this->item->id), 'list');
		ToolbarHelper::apply('question.apply');
		ToolbarHelper::save('question.save');
		ToolbarHelper::cancel('question.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
