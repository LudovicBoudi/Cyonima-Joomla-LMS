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
use Joomla\CMS\Uri\Uri;

$item = $this->item;
?>

<form action="<?php echo Route::_('index.php?option=com_cyonima&view=certificatetemplate&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
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
			<label class="control-label" for="jform_image"><?php echo Text::_('COM_CYONIMA_IMAGE'); ?></label>
			<div class="controls">
				<input type="text" name="jform[image]" id="jform_image" class="form-control"
					value="<?php echo $this->escape($item->image ?? ''); ?>">
				<?php if (!empty($item->image)) : ?>
					<img src="<?php echo $this->escape(Uri::root() . $item->image); ?>" class="img-fluid" alt="">
				<?php endif; ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_image_file"><?php echo Text::_('COM_CYONIMA_IMAGE_FILE'); ?></label>
			<div class="controls">
				<input type="file" name="jform[image_file]" id="jform_image_file" class="form-control">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_params"><?php echo Text::_('COM_CYONIMA_PARAMS'); ?></label>
			<div class="controls">
				<textarea name="jform[params]" id="jform_params" class="form-control" rows="8"><?php echo $this->escape($item->params ?? '{}'); ?></textarea>
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
