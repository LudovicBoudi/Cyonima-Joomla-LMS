<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

/**
 * Base list controller providing the "add" and "edit" navigation actions.
 */
abstract class BaseCyonimaListController extends AdminController
{
	/**
	 * @var  string
	 */
	protected $view_item;

	/**
	 * Open the "new item" edit form.
	 *
	 * @return  void
	 */
	public function add()
	{
		$this->setRedirect(Route::_('index.php?option=com_cyonima&view=' . $this->view_item . '&layout=edit' . $this->getRedirectToItemAppend(), false));
	}

	/**
	 * Open the edit form for the selected item.
	 *
	 * @return  void
	 */
	public function edit()
	{
		$cid = array_filter((array) $this->input->get('cid', [], 'int'));
		$id  = \count($cid) ? (int) $cid[0] : $this->input->getInt('id');

		$this->setRedirect(Route::_('index.php?option=com_cyonima&view=' . $this->view_item . '&layout=edit&id=' . $id, false));
	}

	/**
	 * @return  string
	 */
	protected function getRedirectToItemAppend(): string
	{
		return '';
	}
}
