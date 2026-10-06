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
 * Exam attempt table.
 */
class ExamAttemptTable extends Table
{
	/**
	 * @param   DatabaseDriver  $db  Database driver.
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__cyonima_exam_attempts', 'id', $db);
	}

	public function bind($array, $ignore = '')
	{
		if (isset($array['answers']) && \is_array($array['answers'])) {
			$array['answers'] = json_encode($array['answers']);
		}

		return parent::bind($array, $ignore);
	}
}
