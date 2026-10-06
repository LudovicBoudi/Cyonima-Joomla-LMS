<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$item = $this->item;
?>

<form action="<?php echo Route::_('index.php?option=com_cyonima&view=exam&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset class="options-form">
		<div class="control-group">
			<label class="control-label" for="jform_title"><?php echo Text::_('JGLOBAL_TITLE'); ?></label>
			<div class="controls">
				<input type="text" name="jform[title]" id="jform_title" class="form-control" required
					value="<?php echo $this->escape($item->title); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_course_id"><?php echo Text::_('COM_CYONIMA_COURSE'); ?></label>
			<div class="controls">
				<input type="number" name="jform[course_id]" id="jform_course_id" class="form-control"
					value="<?php echo $this->escape($item->course_id ?? 0); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_lesson_id"><?php echo Text::_('COM_CYONIMA_LESSON'); ?></label>
			<div class="controls">
				<input type="number" name="jform[lesson_id]" id="jform_lesson_id" class="form-control"
					value="<?php echo $this->escape($item->lesson_id ?? 0); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_description"><?php echo Text::_('COM_CYONIMA_DESCRIPTION'); ?></label>
			<div class="controls">
				<textarea name="jform[description]" id="jform_description" class="form-control" rows="3"><?php echo $this->escape($item->description ?? ''); ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_time_limit"><?php echo Text::_('COM_CYONIMA_TIME_LIMIT'); ?></label>
			<div class="controls">
				<input type="number" name="jform[time_limit]" id="jform_time_limit" class="form-control"
					value="<?php echo $this->escape($item->time_limit ?? 0); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_pass_mark"><?php echo Text::_('COM_CYONIMA_PASS_MARK'); ?></label>
			<div class="controls">
				<input type="number" name="jform[pass_mark]" id="jform_pass_mark" class="form-control"
					value="<?php echo $this->escape($item->pass_mark ?? 50); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_attempts_allowed"><?php echo Text::_('COM_CYONIMA_ATTEMPTS_ALLOWED'); ?></label>
			<div class="controls">
				<input type="number" name="jform[attempts_allowed]" id="jform_attempts_allowed" class="form-control"
					value="<?php echo $this->escape($item->attempts_allowed ?? 1); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_shuffle"><?php echo Text::_('COM_CYONIMA_SHUFFLE'); ?></label>
			<div class="controls">
				<select name="jform[shuffle]" id="jform_shuffle" class="form-select">
					<option value="0" <?php echo $item->shuffle == 0 ? 'selected' : ''; ?>><?php echo Text::_('JNO'); ?></option>
					<option value="1" <?php echo $item->shuffle == 1 ? 'selected' : ''; ?>><?php echo Text::_('JYES'); ?></option>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_published"><?php echo Text::_('JSTATUS'); ?></label>
			<div class="controls">
				<select name="jform[published]" id="jform_published" class="form-select">
					<option value="1" <?php echo $item->published == 1 ? 'selected' : ''; ?>><?php echo Text::_('JPUBLISHED'); ?></option>
					<option value="0" <?php echo $item->published == 0 ? 'selected' : ''; ?>><?php echo Text::_('JUNPUBLISHED'); ?></option>
				</select>
			</div>
		</div>
	</fieldset>

	<input type="hidden" name="jform[id]" value="<?php echo (int) $item->id; ?>">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
