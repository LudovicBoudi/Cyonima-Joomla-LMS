<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

/**
 * Base list model providing the bulk operations used by the admin controllers.
 */
abstract class BaseCyonimaListModel extends ListModel
{
	/**
	 * Deletes one or more items.
	 *
	 * @param   array  $pks  Primary keys.
	 *
	 * @return  boolean
	 */
	public function delete(&$pks)
	{
		$pks   = (array) $pks;
		$table = $this->getTable();

		foreach ($pks as $pk) {
			if (!$table->delete((int) $pk)) {
				$this->setError($table->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Publishes or unpublishes one or more items.
	 *
	 * @param   array    $pks    Primary keys.
	 * @param   integer  $value  The new state.
	 *
	 * @return  boolean
	 */
	public function publish(&$pks, $value = 1)
	{
		$pks   = (array) $pks;
		$table = $this->getTable();

		foreach ($pks as $pk) {
			if (!$table->load((int) $pk)) {
				continue;
			}

			$table->published = (int) $value;

			if (!$table->store()) {
				$this->setError($table->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Saves the manual ordering of items.
	 *
	 * @param   array  $pks    Primary keys.
	 * @param   array  $order  Order values.
	 *
	 * @return  boolean
	 */
	public function saveorder($pks = [], $order = null)
	{
		$pks   = (array) $pks;
		$order = (array) $order;
		$table = $this->getTable();

		foreach ($pks as $i => $pk) {
			if (!$table->load((int) $pk)) {
				continue;
			}

			if ($table->ordering != $order[$i]) {
				$table->ordering = (int) $order[$i];

				if (!$table->store()) {
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Reorders one or more items by a delta.
	 *
	 * @param   array    $pks    Primary keys.
	 * @param   integer  $delta  Direction (-1 up, +1 down).
	 *
	 * @return  boolean
	 */
	public function reorder($pks = null, $delta = 0)
	{
		$table = $this->getTable();

		foreach ((array) $pks as $pk) {
			if (!$table->load((int) $pk)) {
				continue;
			}

			$where = [];

			if (property_exists($table, 'course_id') && !empty($table->course_id)) {
				$where = [$table->getDatabase()->quoteName('course_id') . ' = ' . (int) $table->course_id];
			}

			if (!$table->move($delta, $where)) {
				$this->setError($table->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Check-in items.
	 *
	 * @param   array  $pks  Primary keys.
	 *
	 * @return  boolean
	 */
	public function checkin($pks = [])
	{
		$table = $this->getTable();

		foreach ((array) $pks as $pk) {
			if (!$table->load((int) $pk)) {
				continue;
			}

			if (!\method_exists($table, 'checkIn') || !$table->checkIn($pk)) {
				// Tables without checkout support are considered checked-in.
			}
		}

		return true;
	}
}
