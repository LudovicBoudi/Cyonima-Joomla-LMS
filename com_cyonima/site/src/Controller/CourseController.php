<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Controller;

\defined('_JEXEC') or die;

use Cyonima\Component\Cyonima\Administrator\Table\EnrollmentTable;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Course controller (enrollment).
 */
class CourseController extends BaseController
{
	/**
	 * Enroll the current user into the course.
	 *
	 * @return  void
	 */
	public function enroll()
	{
		$this->checkToken();

		$app  = Factory::getApplication();
		$user = $app->getIdentity();
		$id   = (int) $app->input->getInt('id');

		if ($user->guest) {
			$app->enqueueMessage(Text::_('COM_CYONIMA_ERROR_LOGIN_REQUIRED'), 'warning');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Route::_('index.php?option=com_cyonima&view=course&id=' . $id))));

			return;
		}

		if (!$user->authorise('course.enroll', 'com_cyonima')) {
			$app->enqueueMessage(Text::_('COM_CYONIMA_ERROR_ENROLL_FORBIDDEN'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_cyonima&view=course&id=' . $id));

			return;
		}

		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$course = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_courses'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		if (!$course || !$course->published) {
			$app->enqueueMessage(Text::_('COM_CYONIMA_ERROR_COURSE_NOT_FOUND'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_cyonima&view=courses'));

			return;
		}

		$exists = (int) $db->setQuery(
			$db->getQuery(true)
				->select('COUNT(*)')
				->from($db->quoteName('#__cyonima_enrollments'))
				->where($db->quoteName('course_id') . ' = :course')
				->where($db->quoteName('user_id') . ' = :user')
				->bind(':course', $id, ParameterType::INTEGER)
				->bind(':user', $user->id, ParameterType::INTEGER)
		)->loadResult();

		if ($exists) {
			$app->enqueueMessage(Text::_('COM_CYONIMA_ALREADY_ENROLLED'), 'info');
			$this->setRedirect(Route::_('index.php?option=com_cyonima&view=course&id=' . $id));

			return;
		}

		$table = new EnrollmentTable($db);

		$table->bind([
			'course_id'     => $id,
			'user_id'       => (int) $user->id,
			'status'        => 'enrolled',
			'progress'      => 0,
			'enrolled_date' => Factory::getDate()->toSql(),
		]);

		if (!$table->store()) {
			$app->enqueueMessage($table->getError(), 'error');
		} else {
			$app->enqueueMessage(Text::_('COM_CYONIMA_ENROLLED_SUCCESS'), 'message');
		}

		$this->setRedirect(Route::_('index.php?option=com_cyonima&view=course&id=' . $id));
	}
}
