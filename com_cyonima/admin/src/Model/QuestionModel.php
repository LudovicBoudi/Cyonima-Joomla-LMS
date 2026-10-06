<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Single question edit model.
 */
class QuestionModel extends BaseDatabaseModel
{
	public function getItem(int $id = 0)
	{
		$id = $id ?: (int) $this->getState('question.id');

		if (!$id) {
			return (object) [
				'id'        => 0,
				'exam_id'   => (int) $this->getState('question.exam_id', 0),
				'question'  => '',
				'type'      => 'single',
				'options'   => '[]',
				'answer'    => '0',
				'points'    => 1,
				'ordering'  => 0,
				'published' => 1,
			];
		}

		$db = $this->getDatabase();

		$item = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_questions'))
				->where($db->quoteName('id') . ' = ' . $id)
		)->loadObject();

		return $item ?: null;
	}

	public function save(array $data)
	{
		if (isset($data['options_text'])) {
			$options = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data['options_text']))));
			$data['options'] = $options;
			unset($data['options_text']);
		}

		if (!\is_array($data['options'] ?? null)) {
			$data['options'] = [];
		}

		$type = $data['type'] ?? 'single';

		if ($type === 'multiple') {
			if (isset($data['answer_text'])) {
				$answer = array_values(array_filter(array_map('intval', explode(',', $data['answer_text']))));
				$data['answer'] = $answer;
				unset($data['answer_text']);
			} elseif (!\is_array($data['answer'] ?? null)) {
				$data['answer'] = [(int) ($data['answer'] ?? 0)];
			}
		} else {
			if (isset($data['answer_text'])) {
				$data['answer'] = (int) $data['answer_text'];
				unset($data['answer_text']);
			} else {
				$data['answer'] = (int) ($data['answer'] ?? 0);
			}
		}

		$table = $this->getMVCFactory()->createTable('Question');

		if (!empty($data['id'])) {
			$table->load((int) $data['id']);
		}

		if (!$table->save($data)) {
			$this->setError($table->getError());

			return false;
		}

		$this->setState('question.id', (int) $table->id);

		return (int) $table->id;
	}
}
