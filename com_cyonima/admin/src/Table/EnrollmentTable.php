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
 * Enrollment table.
 */
class EnrollmentTable extends Table
{
	/**
	 * @param   DatabaseDriver  $db  Database driver.
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__cyonima_enrollments', 'id', $db);
	}

	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && \is_array($array['params'])) {
			$array['params'] = json_encode($array['params']);
		}

		return parent::bind($array, $ignore);
	}
}
