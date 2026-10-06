<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

/**
 * Base controller for the single-item edit (save/apply/cancel) views.
 */
abstract class BaseCyonimaFormController extends AdminController
{
	/**
	 * @var  string
	 */
	protected $view_list;

	/**
	 * @var  string
	 */
	protected $view_item;

	/**
	 * @var  string
	 */
	protected $model_name;

	/**
	 * Save and close.
	 *
	 * @return  void
	 */
	public function save()
	{
		$this->persist('save');
	}

	/**
	 * Save and keep editing.
	 *
	 * @return  void
	 */
	public function apply()
	{
		$this->persist('apply');
	}

	/**
	 * Persist the posted data.
	 *
	 * @param   string  $task  'save' (close) or 'apply' (stay).
	 *
	 * @return  void
	 */
	protected function persist(string $task): void
	{
		$this->checkToken();

		$data  = $this->input->post->get('jform', [], 'array');
		$model = $this->getModel($this->model_name, 'Administrator');
		$id    = $model->save($data);

		if (!$id) {
			$this->setMessage($model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_cyonima&view=' . $this->view_list, false));

			return;
		}

		$this->setMessage(Text::_('COM_CYONIMA_SAVED'));

		if ($task === 'apply') {
			$this->setRedirect(Route::_('index.php?option=com_cyonima&view=' . $this->view_item . '&layout=edit&id=' . $id, false));
		} else {
			$this->setRedirect(Route::_('index.php?option=com_cyonima&view=' . $this->view_list, false));
		}
	}

	/**
	 * Cancel editing.
	 *
	 * @return  void
	 */
	public function cancel()
	{
		$this->checkToken();
		$this->setRedirect(Route::_('index.php?option=com_cyonima&view=' . $this->view_list, false));
	}
}
