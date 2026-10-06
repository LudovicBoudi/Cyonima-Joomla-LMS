<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('stylesheet', 'com_cyonima/cyonima.css', ['version' => 'auto', 'relative' => true]);

$lesson = $this->lesson;
?>

<div class="cyonima cyonima-lesson-player">
	<?php if (!$lesson) : ?>
		<p><?php echo Text::_('COM_CYONIMA_ERROR_LESSON_NOT_FOUND'); ?></p>
		<?php return; ?>
	<?php endif; ?>

	<nav>
		<a href="<?php echo Route::_('index.php?option=com_cyonima&view=course&id=' . (int) $lesson->course_id); ?>">
			&larr; <?php echo $this->escape($lesson->course_title); ?>
		</a>
	</nav>

	<h1><?php echo $this->escape($lesson->title); ?></h1>

	<?php if ($lesson->description) : ?>
		<p class="cyonima-intro"><?php echo $this->escape($lesson->description); ?></p>
	<?php endif; ?>

	<?php switch ($lesson->type) : case 'video': ?>
		<?php if ($lesson->media) : ?>
			<div class="cyonima-video">
				<video controls preload="metadata" style="max-width:100%;">
					<source src="<?php echo $this->escape($lesson->media); ?>" type="video/mp4">
					<?php echo Text::_('COM_CYONIMA_VIDEO_UNSUPPORTED'); ?>
				</video>
			</div>
		<?php elseif ($lesson->url) : ?>
			<div class="cyonima-embed">
				<iframe src="<?php echo $this->escape($lesson->url); ?>" frameborder="0" allowfullscreen></iframe>
			</div>
		<?php endif; ?>
		<?php break; ?>

	<?php case 'pdf': ?>
		<?php if ($lesson->media) : ?>
			<div class="cyonima-pdf">
				<iframe src="<?php echo $this->escape($lesson->media); ?>" width="100%" height="700px"></iframe>
			</div>
		<?php elseif ($lesson->url) : ?>
			<p><a class="btn btn-primary" target="_blank" rel="noopener" href="<?php echo $this->escape($lesson->url); ?>"><?php echo Text::_('COM_CYONIMA_OPEN_PDF'); ?></a></p>
		<?php endif; ?>
		<?php break; ?>

	<?php case 'link': ?>
		<?php if ($lesson->url) : ?>
			<p><a class="btn btn-primary" target="_blank" rel="noopener" href="<?php echo $this->escape($lesson->url); ?>"><?php echo Text::_('COM_CYONIMA_OPEN_EXTERNAL'); ?></a></p>
		<?php endif; ?>
		<?php break; ?>

	<?php case 'assignment': ?>
		<?php $assignmentId = $lesson->assignment_id ?? 0; ?>
		<?php if ($assignmentId) : ?>
			<p><a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_cyonima&view=assignment&id=' . (int) $assignmentId); ?>"><?php echo Text::_('COM_CYONIMA_GO_ASSIGNMENT'); ?></a></p>
		<?php endif; ?>
		<?php break; ?>

	<?php case 'exam': ?>
		<?php $examId = $lesson->exam_id ?? 0; ?>
		<?php if ($examId) : ?>
			<p><a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_cyonima&view=exam&id=' . (int) $examId); ?>"><?php echo Text::_('COM_CYONIMA_GO_EXAM'); ?></a></p>
		<?php endif; ?>
		<?php break; ?>

	<?php default: ?>
		<?php if ($lesson->content) : ?>
			<div class="cyonima-content"><?php echo HTMLHelper::_('content.prepare', $lesson->content); ?></div>
		<?php endif; ?>
	<?php endswitch; ?>

	<?php if (!\in_array($lesson->type, ['assignment', 'exam'], true)) : ?>
		<form action="<?php echo Route::_('index.php?option=com_cyonima&task=lesson.complete&id=' . (int) $lesson->id); ?>" method="post">
			<?php echo HTMLHelper::_('form.token'); ?>
			<button type="submit" class="btn btn-success">
				<?php echo $this->completed ? Text::_('COM_CYONIMA_COMPLETED') : Text::_('COM_CYONIMA_MARK_COMPLETE'); ?>
			</button>
		</form>
	<?php endif; ?>
</div>
