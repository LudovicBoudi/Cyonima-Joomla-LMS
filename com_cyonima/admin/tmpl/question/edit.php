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

<form action="<?php echo Route::_('index.php?option=com_cyonima&view=question&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset class="options-form">
		<div class="control-group">
			<label class="control-label" for="jform_exam_id"><?php echo Text::_('COM_CYONIMA_EXAM'); ?></label>
			<div class="controls">
				<input type="number" name="jform[exam_id]" id="jform_exam_id" class="form-control"
					value="<?php echo $this->escape($item->exam_id ?? 0); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_question"><?php echo Text::_('COM_CYONIMA_QUESTION'); ?></label>
			<div class="controls">
				<textarea name="jform[question]" id="jform_question" class="form-control" rows="3"><?php echo $this->escape($item->question ?? ''); ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_type"><?php echo Text::_('COM_CYONIMA_TYPE'); ?></label>
			<div class="controls">
				<select name="jform[type]" id="jform_type" class="form-select">
					<?php foreach (['single', 'multiple', 'truefalse'] as $type) : ?>
						<option value="<?php echo $type; ?>" <?php echo $item->type === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_options_text"><?php echo Text::_('COM_CYONIMA_OPTIONS'); ?></label>
			<div class="controls">
				<textarea name="jform[options_text]" id="jform_options_text" class="form-control" rows="5"><?php echo $this->escape($item->options_text ?? ''); ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_answer_text"><?php echo Text::_('COM_CYONIMA_ANSWER'); ?></label>
			<div class="controls">
				<input type="text" name="jform[answer_text]" id="jform_answer_text" class="form-control"
					value="<?php echo $this->escape($item->answer_text ?? ''); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_points"><?php echo Text::_('COM_CYONIMA_POINTS'); ?></label>
			<div class="controls">
				<input type="number" name="jform[points]" id="jform_points" class="form-control"
					value="<?php echo $this->escape($item->points ?? 1); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_ordering"><?php echo Text::_('COM_CYONIMA_ORDERING'); ?></label>
			<div class="controls">
				<input type="number" name="jform[ordering]" id="jform_ordering" class="form-control"
					value="<?php echo $this->escape($item->ordering ?? 0); ?>">
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
