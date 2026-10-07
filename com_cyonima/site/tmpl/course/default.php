<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Cyonima\Component\Cyonima\Administrator\Helper\CyonimaHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('stylesheet', 'com_cyonima/cyonima.css', ['version' => 'auto', 'relative' => true]);

$course = $this->course;
?>

<div class="cyonima cyonima-course">
	<?php if (!$course) : ?>
		<p><?php echo Text::_('COM_CYONIMA_ERROR_COURSE_NOT_FOUND'); ?></p>
		<?php return; ?>
	<?php endif; ?>

	<h1><?php echo $this->escape($course->title); ?></h1>

	<?php if ($course->teacher) : ?>
		<div class="cyonima-muted"><?php echo Text::sprintf('COM_CYONIMA_BY_TEACHER', $this->escape($course->teacher)); ?></div>
	<?php endif; ?>

	<?php if ($course->intro) : ?>
		<div class="cyonima-intro"><?php echo $this->escape($course->intro); ?></div>
	<?php endif; ?>

	<?php if ($course->description) : ?>
		<div class="cyonima-description"><?php echo HTMLHelper::_('content.prepare', $course->description); ?></div>
	<?php endif; ?>

	<?php if ($this->enrollment) : ?>
		<div class="cyonima-enrollment-status">
			<?php echo Text::sprintf('COM_CYONIMA_PROGRESS_PERCENT', (int) $this->enrollment->progress); ?>
			<?php if ($this->enrollment->max_score > 0) : ?>
				&middot; <?php echo Text::sprintf('COM_CYONIMA_GLOBAL_GRADE_VALUE', round($this->enrollment->score, 1)); ?>
			<?php endif; ?>
		</div>

		<?php if ($this->enrollment->status === 'completed') : ?>
			<p>
				<a class="btn btn-success" href="<?php echo Route::_('index.php?option=com_cyonima&view=certificates'); ?>">
					<?php echo Text::_('COM_CYONIMA_VIEW_CERTIFICATE'); ?>
				</a>
			</p>
		<?php endif; ?>
	<?php else : ?>
		<?php $user = \Joomla\CMS\Factory::getApplication()->getIdentity(); ?>
		<?php if (!$user->guest) : ?>
			<form action="<?php echo Route::_('index.php?option=com_cyonima&task=course.enroll&id=' . (int) $course->id); ?>" method="post">
				<?php echo HTMLHelper::_('form.token'); ?>
				<button type="submit" class="btn btn-primary"><?php echo Text::_('COM_CYONIMA_ENROLL'); ?></button>
			</form>
		<?php else : ?>
			<p>
				<a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Route::_('index.php?option=com_cyonima&view=course&id=' . (int) $course->id))); ?>">
					<?php echo Text::_('COM_CYONIMA_LOGIN_TO_ENROLL'); ?>
				</a>
			</p>
		<?php endif; ?>
	<?php endif; ?>

	<?php if (!empty($this->lessons)) : ?>
		<h2><?php echo Text::_('COM_CYONIMA_COURSE_CONTENT'); ?></h2>
		<ul class="cyonima-lessons">
			<?php foreach ($this->lessons as $lesson) : ?>
				<?php
				$isCompleted = ($this->completed[$lesson->id] ?? '') === 'completed';
				$lessonLink  = Route::_('index.php?option=com_cyonima&view=lesson&id=' . (int) $lesson->id . ':' . $lesson->alias);
				?>
				<li class="cyonima-lesson <?php echo $isCompleted ? 'is-completed' : ''; ?>">
					<a href="<?php echo $lessonLink; ?>">
						<span class="cyonima-lesson__type"><?php echo $this->escape($lesson->type); ?></span>
						<span class="cyonima-lesson__title"><?php echo $this->escape($lesson->title); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
