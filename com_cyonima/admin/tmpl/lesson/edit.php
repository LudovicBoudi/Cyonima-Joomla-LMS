<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Cyonima\Component\Cyonima\Administrator\Helper\CyonimaHelper;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$item = $this->item;
?>

<?php HTMLHelper::_('behavior.formvalidator'); ?>
<form action="<?php echo Route::_('index.php?option=com_cyonima&view=lesson&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
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
			<label class="control-label" for="jform_type"><?php echo Text::_('COM_CYONIMA_TYPE'); ?></label>
			<div class="controls">
				<select name="jform[type]" id="jform_type" class="form-select">
					<?php foreach (['content', 'video', 'pdf', 'link', 'assignment', 'exam'] as $type) : ?>
						<option value="<?php echo $type; ?>" <?php echo $item->type === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
					<?php endforeach; ?>
				</select>
				<small class="form-text text-muted"><?php echo Text::_('COM_CYONIMA_LESSON_TYPE_HINT'); ?></small>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_description"><?php echo Text::_('COM_CYONIMA_DESCRIPTION'); ?></label>
			<div class="controls">
				<textarea name="jform[description]" id="jform_description" class="form-control" rows="3"><?php echo $this->escape($item->description ?? ''); ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_content"><?php echo Text::_('COM_CYONIMA_CONTENT'); ?></label>
			<div class="controls">
				<?php echo Editor::getInstance()->display('jform[content]', $item->content ?? '', '100%', '300px', 60, 20); ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_url"><?php echo Text::_('COM_CYONIMA_URL'); ?></label>
			<div class="controls">
				<input type="text" name="jform[url]" id="jform_url" class="form-control"
					value="<?php echo $this->escape($item->url ?? ''); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_media"><?php echo Text::_('COM_CYONIMA_MEDIA'); ?></label>
			<div class="controls">
				<?php echo CyonimaHelper::mediaField('jform[media]', $item->media ?? '', 'jform_media'); ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_duration"><?php echo Text::_('COM_CYONIMA_DURATION'); ?></label>
			<div class="controls">
				<input type="number" name="jform[duration]" id="jform_duration" class="form-control"
					value="<?php echo $this->escape($item->duration ?? 0); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_max_score"><?php echo Text::_('COM_CYONIMA_MAX_SCORE'); ?></label>
			<div class="controls">
				<input type="number" name="jform[max_score]" id="jform_max_score" class="form-control"
					value="<?php echo $this->escape($item->max_score ?? 0); ?>">
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
		<div class="control-group">
			<label class="control-label" for="jform_access"><?php echo Text::_('JFIELD_ACCESS_LABEL'); ?></label>
			<div class="controls">
				<?php echo HTMLHelper::_('access.assetgrouplist', 'jform[access]', $item->access ?? 1, 'class="form-select"'); ?>
			</div>
		</div>
	</fieldset>

	<input type="hidden" name="jform[id]" value="<?php echo (int) $item->id; ?>">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
