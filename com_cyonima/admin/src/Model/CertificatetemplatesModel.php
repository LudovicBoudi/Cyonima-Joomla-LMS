<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Model;

\defined('_JEXEC') or die;


/**
 * Certificate templates list model.
 */
class CertificatetemplatesModel extends BaseCyonimaListModel
{
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return $this->getMVCFactory()->createTable('Certificatetemplate');
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('t.id'),
				$db->quoteName('t.title'),
				$db->quoteName('t.image'),
				$db->quoteName('t.published'),
				$db->quoteName('u.name', 'teacher'),
			]
		)
			->from($db->quoteName('#__cyonima_certificate_templates', 't'))
			->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('t.user_id'))
			->order($db->quoteName('t.title') . ' ASC');

		return $query;
	}
}
