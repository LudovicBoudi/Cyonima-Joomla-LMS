<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Question table.
 */
class QuestionTable extends Table
{
	/**
	 * @param   DatabaseDriver  $db  Database driver.
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__cyonima_questions', 'id', $db);
	}

	public function bind($array, $ignore = '')
	{
		if (isset($array['options']) && \is_array($array['options'])) {
			$array['options'] = json_encode($array['options']);
		}

		if (isset($array['answer']) && \is_array($array['answer'])) {
			$array['answer'] = json_encode($array['answer']);
		}

		return parent::bind($array, $ignore);
	}
}
