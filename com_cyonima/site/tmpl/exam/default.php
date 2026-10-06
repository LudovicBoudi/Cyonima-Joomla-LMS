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

$exam = $this->exam;
?>

<div class="cyonima cyonima-exam">
	<?php if (!$exam) : ?>
		<p><?php echo Text::_('COM_CYONIMA_ERROR_EXAM_NOT_FOUND'); ?></p>
		<?php return; ?>
	<?php endif; ?>

	<h1><?php echo $this->escape($exam->title); ?></h1>

	<?php if ($exam->description) : ?>
		<p><?php echo $this->escape($exam->description); ?></p>
	<?php endif; ?>

	<?php if ($this->attemptId) : ?>
		<form action="<?php echo Route::_('index.php?option=com_cyonima&task=exam.submit&id=' . (int) $exam->id); ?>" method="post">
			<?php echo HTMLHelper::_('form.token'); ?>
			<input type="hidden" name="attempt" value="<?php echo (int) $this->attemptId; ?>">

			<?php foreach ($this->questions as $i => $question) : ?>
				<fieldset class="cyonima-question">
					<legend><?php echo ($i + 1) . '. ' . $this->escape($question->question); ?></legend>

					<?php if ($question->type === 'single') : ?>
						<?php foreach ($question->options as $oi => $option) : ?>
							<label>
								<input type="radio" name="answers[<?php echo (int) $question->id; ?>]" value="<?php echo (int) $oi; ?>">
								<?php echo $this->escape($option); ?>
							</label><br>
						<?php endforeach; ?>

					<?php elseif ($question->type === 'multiple') : ?>
						<?php foreach ($question->options as $oi => $option) : ?>
							<label>
								<input type="checkbox" name="answers[<?php echo (int) $question->id; ?>][]" value="<?php echo (int) $oi; ?>">
								<?php echo $this->escape($option); ?>
							</label><br>
						<?php endforeach; ?>

					<?php else : ?>
						<?php foreach ($question->options as $oi => $option) : ?>
							<label>
								<input type="radio" name="answers[<?php echo (int) $question->id; ?>]" value="<?php echo (int) $oi; ?>">
								<?php echo $this->escape($option); ?>
							</label><br>
						<?php endforeach; ?>
					<?php endif; ?>
				</fieldset>
			<?php endforeach; ?>

			<button type="submit" class="btn btn-primary"><?php echo Text::_('COM_CYONIMA_SUBMIT_EXAM'); ?></button>
		</form>

	<?php else : ?>
		<?php if ($this->attempts) : ?>
			<h2><?php echo Text::_('COM_CYONIMA_EXAM_RESULTS'); ?></h2>
			<ul>
				<?php foreach ($this->attempts as $attempt) : ?>
					<li>
						<?php echo Text::sprintf('COM_CYONIMA_EXAM_RESULT_ROW', $this->escape($attempt->score), $this->escape($attempt->max_score), $attempt->passed ? Text::_('JYES') : Text::_('JNO')); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ($this->canAttempt) : ?>
			<form action="<?php echo Route::_('index.php?option=com_cyonima&task=exam.start&id=' . (int) $exam->id); ?>" method="post">
				<?php echo HTMLHelper::_('form.token'); ?>
				<button type="submit" class="btn btn-primary"><?php echo Text::_('COM_CYONIMA_START_EXAM'); ?></button>
			</form>
		<?php else : ?>
			<p><?php echo Text::_('COM_CYONIMA_NO_ATTEMPTS_LEFT'); ?></p>
		<?php endif; ?>
	<?php endif; ?>
</div>
