<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\View\Dashboard;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Student dashboard view.
 */
class HtmlView extends BaseHtmlView
{
	public $items;

	public function display($tpl = null)
	{
		$this->items = $this->get('Items');

		parent::display($tpl);
	}
}
