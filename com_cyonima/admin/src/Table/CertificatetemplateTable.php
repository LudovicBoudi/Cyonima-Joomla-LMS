<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Certificate template table.
 */
class CertificatetemplateTable extends Table
{
	/**
	 * @param   DatabaseDriver  $db  Database driver.
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__cyonima_certificate_templates', 'id', $db);
	}

	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && \is_array($array['params'])) {
			$array['params'] = json_encode($array['params']);
		}

		return parent::bind($array, $ignore);
	}

	public function check()
	{
		if (trim($this->title) === '') {
			$this->setError(\Joomla\CMS\Language\Text::_('COM_CYONIMA_ERROR_TITLE_REQUIRED'));

			return false;
		}

		return true;
	}

	public function store($updateNulls = true)
	{
		$date = Factory::getDate();
		$user = Factory::getApplication()->getIdentity();

		if ($this->id) {
			// Nothing to update on edit timestamp-wise.
		} else {
			if (!(int) $this->created) {
				$this->created = $date->toSql();
			}

			if (empty($this->user_id)) {
				$this->user_id = (int) $user->id;
			}
		}

		return parent::store($updateNulls);
	}
}
