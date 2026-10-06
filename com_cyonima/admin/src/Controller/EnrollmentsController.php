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
 * Enrollments list controller.
 */
class EnrollmentsController extends AdminController
{
	/**
	 * Remove a student from a course.
	 *
	 * @return  void
	 */
	public function remove()
	{
		$this->checkToken();

		$cid = array_filter((array) $this->input->get('cid', [], 'int'));

		$table = $this->getMVCFactory()->createTable('Enrollment');

		foreach ($cid as $id) {
			$table->delete((int) $id);
		}

		$this->setMessage(Text::_('COM_CYONIMA_ENROLLMENT_REMOVED'));
		$this->setRedirect(Route::_('index.php?option=com_cyonima&view=enrollments', false));
	}
}
