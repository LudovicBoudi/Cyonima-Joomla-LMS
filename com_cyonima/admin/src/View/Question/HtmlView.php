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
use Joomla\Database\DatabaseInterface;

/**
 * Question edit view.
 */
class HtmlView extends BaseHtmlView
{
	public $item;

	public $exams;

	public $assignments;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$input = Factory::getApplication()->input;

		$model->setState('question.id', $input->getInt('id'));

		if (!$input->getInt('id')) {
			$model->setState('question.exam_id', $input->getInt('exam_id'));
			$model->setState('question.assignment_id', $input->getInt('assignment_id'));
		}

		$this->item = $model->getItem();

		if ($this->item) {
			$options = json_decode($this->item->options ?: '[]', true);
			$options = \is_array($options) ? $options : [];

			$answer = json_decode($this->item->answer ?: '0', true);

			if (!\is_array($answer)) {
				$answer = [(int) $answer];
			} elseif ($this->item->type === 'multiple') {
				$answer = array_map('intval', $answer);
			} else {
				$answer = [(int) reset($answer)];
			}

			$rows = [];

			foreach ($options as $index => $text) {
				$rows[] = [
					'text'    => (string) $text,
					'correct' => \in_array($index, $answer, true),
				];
			}

			// Make sure a brand new question starts with two empty answer slots.
			while (\count($rows) < 2) {
				$rows[] = ['text' => '', 'correct' => false];
			}

			$this->item->optionRows = $rows;

			$this->item->parent = $this->item->exam_id ? 'exam:' . (int) $this->item->exam_id
				: ($this->item->assignment_id ? 'assignment:' . (int) $this->item->assignment_id : '');
		}

		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$this->exams = $db->setQuery(
			$db->getQuery(true)
				->select([$db->quoteName('id'), $db->quoteName('title')])
				->from($db->quoteName('#__cyonima_exams'))
				->where($db->quoteName('published') . ' = 1')
				->order($db->quoteName('title') . ' ASC')
		)->loadObjectList() ?: [];

		$this->assignments = $db->setQuery(
			$db->getQuery(true)
				->select([$db->quoteName('id'), $db->quoteName('title')])
				->from($db->quoteName('#__cyonima_assignments'))
				->where($db->quoteName('published') . ' = 1')
				->order($db->quoteName('title') . ' ASC')
		)->loadObjectList() ?: [];

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
