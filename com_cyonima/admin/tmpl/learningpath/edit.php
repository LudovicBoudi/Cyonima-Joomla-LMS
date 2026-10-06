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

<form action="<?php echo Route::_('index.php?option=com_cyonima&view=learningpath&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset class="options-form">
		<div class="control-group">
			<label class="control-label" for="jform_title"><?php echo Text::_('JGLOBAL_TITLE'); ?></label>
			<div class="controls">
				<input type="text" name="jform[title]" id="jform_title" class="form-control" required
					value="<?php echo $this->escape($item->title); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_description"><?php echo Text::_('COM_CYONIMA_DESCRIPTION'); ?></label>
			<div class="controls">
				<?php echo HTMLHelper::_('editor', $item->description ?? '', 'jform[description]', ['id' => 'jform_description', 'width' => '100%', 'height' => '300px']); ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_courses"><?php echo Text::_('COM_CYONIMA_COURSES'); ?></label>
			<div class="controls">
				<select name="jform[courses][]" id="jform_courses" class="form-select" multiple size="10">
					<?php foreach ($this->courses as $course) : ?>
						<option value="<?php echo (int) $course->id; ?>" <?php echo in_array((int) $course->id, $this->selectedCourses, true) ? 'selected' : ''; ?>><?php echo $this->escape($course->title); ?></option>
					<?php endforeach; ?>
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
