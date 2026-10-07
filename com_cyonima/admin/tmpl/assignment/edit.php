<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$item = $this->item;
?>

<?php HTMLHelper::_('behavior.formvalidator'); ?>
<form action="<?php echo Route::_('index.php?option=com_cyonima&view=assignment&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
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
				<?php echo Editor::getInstance()->display('jform[description]', $item->description ?? '', '100%', '300px', 60, 20); ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_due_date"><?php echo Text::_('COM_CYONIMA_DUE_DATE'); ?></label>
			<div class="controls">
				<input type="text" name="jform[due_date]" id="jform_due_date" class="form-control"
					value="<?php echo $this->escape($item->due_date ?? ''); ?>"
					placeholder="YYYY-MM-DD HH:MM:SS">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_coefficient"><?php echo Text::_('COM_CYONIMA_COEFFICIENT'); ?></label>
			<div class="controls">
				<input type="number" step="0.01" min="0" name="jform[coefficient]" id="jform_coefficient" class="form-control"
					value="<?php echo $this->escape($item->coefficient ?? 1); ?>">
				<span class="form-control-plaintext small"><?php echo Text::_('COM_CYONIMA_COEFFICIENT_HINT'); ?></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_attempts_allowed"><?php echo Text::_('COM_CYONIMA_ATTEMPTS_ALLOWED'); ?></label>
			<div class="controls">
				<input type="number" min="0" name="jform[attempts_allowed]" id="jform_attempts_allowed" class="form-control"
					value="<?php echo $this->escape($item->attempts_allowed ?? 1); ?>">
				<span class="form-control-plaintext small"><?php echo Text::_('COM_CYONIMA_ATTEMPTS_ALLOWED_HINT'); ?></span>
			</div>
		</div>
		<?php if (!empty($item->id)) : ?>
			<div class="control-group">
				<label class="control-label"><?php echo Text::_('COM_CYONIMA_QUESTIONS'); ?></label>
				<div class="controls">
					<p class="form-control-plaintext">
						<a href="<?php echo Route::_('index.php?option=com_cyonima&view=questions&filter_assignment=' . (int) $item->id); ?>">
							<?php echo Text::_('COM_CYONIMA_MANAGE_QUESTIONS'); ?>
						</a>
					</p>
				</div>
			</div>
		<?php endif; ?>
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
