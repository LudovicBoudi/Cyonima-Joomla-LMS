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

$lesson = $this->lesson;

// Media lessons play the picked file, whatever its real type: the player follows the
// MIME type of the stored file, not only the declared lesson type.
$mediaUrl  = '';
$mediaMime = '';
$player    = '';
$openLabel = '';

if ($lesson && \in_array($lesson->type, ['video', 'pdf'], true)) {
	$mediaUrl  = CyonimaHelper::mediaUrl((string) $lesson->media);
	$mediaMime = $mediaUrl !== '' ? CyonimaHelper::mediaMime($mediaUrl) : '';

	if ($mediaUrl !== '') {
		if ($mediaMime !== '' && strpos($mediaMime, 'audio/') === 0) {
			$player = 'audio';
		} elseif ($mediaMime === 'application/pdf') {
			$player = 'pdf';
		} elseif ($mediaMime !== '' && strpos($mediaMime, 'image/') === 0) {
			$player = 'image';
		} else {
			$player = $lesson->type === 'pdf' && $mediaMime === '' ? 'pdf' : 'video';
		}

		$openLabel = [
			'audio' => 'COM_CYONIMA_OPEN_AUDIO',
			'image' => 'COM_CYONIMA_OPEN_IMAGE',
			'pdf'   => 'COM_CYONIMA_OPEN_PDF',
		][$player] ?? 'COM_CYONIMA_OPEN_VIDEO';
	}
}
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

	<?php if ($player !== '') : ?>
		<div class="cyonima-media cyonima-media--<?php echo $player; ?>">
			<?php if ($player === 'audio') : ?>
				<audio controls preload="metadata" src="<?php echo $this->escape($mediaUrl); ?>"></audio>
			<?php elseif ($player === 'pdf') : ?>
				<iframe src="<?php echo $this->escape($mediaUrl); ?>" title="<?php echo $this->escape($lesson->title); ?>" loading="lazy"></iframe>
			<?php elseif ($player === 'image') : ?>
				<img src="<?php echo $this->escape($mediaUrl); ?>" alt="<?php echo $this->escape($lesson->title); ?>">
			<?php else : ?>
				<video controls preload="metadata" playsinline>
					<source src="<?php echo $this->escape($mediaUrl); ?>"<?php echo $mediaMime !== '' ? ' type="' . $mediaMime . '"' : ''; ?>>
					<?php echo Text::_('COM_CYONIMA_VIDEO_UNSUPPORTED'); ?>
				</video>
			<?php endif; ?>

			<p class="cyonima-media-actions">
				<a class="btn btn-sm btn-outline-secondary" href="<?php echo $this->escape($mediaUrl); ?>" target="_blank" rel="noopener">
					<?php echo Text::_($openLabel); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>

	<?php switch ($lesson->type) : case 'video': ?>
		<?php if ($mediaUrl === '') : ?>
			<?php if ($lesson->url) : ?>
				<div class="cyonima-embed">
					<iframe src="<?php echo $this->escape($lesson->url); ?>" frameborder="0" allowfullscreen></iframe>
				</div>
			<?php else : ?>
				<p class="cyonima-muted"><?php echo Text::_('COM_CYONIMA_NO_MEDIA'); ?></p>
			<?php endif; ?>
		<?php endif; ?>
		<?php break; ?>

	<?php case 'pdf': ?>
		<?php if ($mediaUrl === '') : ?>
			<?php if ($lesson->url) : ?>
				<p><a class="btn btn-primary" target="_blank" rel="noopener" href="<?php echo $this->escape($lesson->url); ?>"><?php echo Text::_('COM_CYONIMA_OPEN_PDF'); ?></a></p>
			<?php else : ?>
				<p class="cyonima-muted"><?php echo Text::_('COM_CYONIMA_NO_MEDIA'); ?></p>
			<?php endif; ?>
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
