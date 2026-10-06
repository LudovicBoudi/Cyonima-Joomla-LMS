<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Site\Controller;

\defined('_JEXEC') or die;

use Cyonima\Component\Cyonima\Administrator\Helper\ProgressHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;

/**
 * Lesson controller (marking completion).
 */
class LessonController extends BaseController
{
	/**
	 * Mark the current lesson complete for the current user.
	 *
	 * @return  void
	 */
	public function complete()
	{
		$this->checkToken();

		$app  = Factory::getApplication();
		$user = $app->getIdentity();
		$id   = (int) $app->input->getInt('id');

		$db = $this->getDatabase();

		$lesson = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_lessons'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER)
		)->loadObject();

		$redirect = Route::_('index.php?option=com_cyonima&view=course&id=' . (int) ($lesson->course_id ?? 0));

		if (!$lesson || $user->guest) {
			$this->setRedirect($redirect);

			return;
		}

		$enrollment = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_enrollments'))
				->where($db->quoteName('course_id') . ' = :course')
				->where($db->quoteName('user_id') . ' = :user')
				->bind(':course', $lesson->course_id, ParameterType::INTEGER)
				->bind(':user', $user->id, ParameterType::INTEGER)
		)->loadObject();

		if (!$enrollment) {
			$app->enqueueMessage(Text::_('COM_CYONIMA_ERROR_NOT_ENROLLED'), 'warning');
			$this->setRedirect($redirect);

			return;
		}

		$progress = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__cyonima_lesson_progress'))
				->where($db->quoteName('enrollment_id') . ' = :enrollment')
				->where($db->quoteName('lesson_id') . ' = :lesson')
				->bind(':enrollment', $enrollment->id, ParameterType::INTEGER)
				->bind(':lesson', $id, ParameterType::INTEGER)
		)->loadObject();

		$table = $this->getMVCFactory()->createTable('LessonProgress');

		if ($progress) {
			$table->load($progress->id);
		}

		$table->bind([
			'enrollment_id'  => (int) $enrollment->id,
			'lesson_id'      => $id,
			'user_id'        => (int) $user->id,
			'status'         => 'completed',
			'completed_date' => Factory::getDate()->toSql(),
		]);
		$table->store();

		ProgressHelper::recalculate((int) $enrollment->id);

		$this->setRedirect($redirect);
	}
}
