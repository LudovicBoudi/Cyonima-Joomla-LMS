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

$assignment = $this->assignment;
?>

<div class="cyonima cyonima-assignment">
	<?php if (!$assignment) : ?>
		<p><?php echo Text::_('COM_CYONIMA_ERROR_ASSIGNMENT_NOT_FOUND'); ?></p>
		<?php return; ?>
	<?php endif; ?>

	<nav>
		<a href="<?php echo Route::_('index.php?option=com_cyonima&view=course&id=' . (int) $assignment->course_id); ?>">
			&larr; <?php echo $this->escape($assignment->course_title); ?>
		</a>
	</nav>

	<h1><?php echo $this->escape($assignment->title); ?></h1>

	<div><?php echo HTMLHelper::_('content.prepare', $assignment->description); ?></div>

	<?php if ($assignment->due_date !== '0000-00-00 00:00:00' && $assignment->due_date) : ?>
		<p class="cyonima-muted"><?php echo Text::sprintf('COM_CYONIMA_ASSIGNMENT_DUE', $this->escape($assignment->due_date)); ?></p>
	<?php endif; ?>

	<?php if ($this->submission && $this->submission->status !== 'graded') : ?>
		<div class="alert alert-info"><?php echo Text::_('COM_CYONIMA_SUBMISSION_PENDING'); ?></div>
	<?php elseif ($this->submission) : ?>
		<div class="alert alert-success">
			<?php echo Text::sprintf('COM_CYONIMA_SUBMISSION_GRADED', $this->escape($this->submission->score), $this->escape($assignment->max_score)); ?>
			<?php if ($this->submission->feedback) : ?>
				<p><?php echo $this->escape($this->submission->feedback); ?></p>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<form action="<?php echo Route::_('index.php?option=com_cyonima&task=assignment.submit&id=' . (int) $assignment->id); ?>" method="post" enctype="multipart/form-data">
			<?php echo HTMLHelper::_('form.token'); ?>
			<div class="control-group">
				<label for="assignment-content"><?php echo Text::_('COM_CYONIMA_ASSIGNMENT_ANSWER'); ?></label>
				<textarea class="form-control" name="content" id="assignment-content" rows="8"></textarea>
			</div>
			<div class="control-group">
				<label for="assignment-file"><?php echo Text::_('COM_CYONIMA_ASSIGNMENT_FILE'); ?></label>
				<input type="file" name="file" id="assignment-file" class="form-control">
			</div>
			<button type="submit" class="btn btn-primary"><?php echo Text::_('COM_CYONIMA_SUBMIT'); ?></button>
		</form>
	<?php endif; ?>
</div>
