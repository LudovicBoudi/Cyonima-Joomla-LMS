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

<form action="<?php echo Route::_('index.php?option=com_cyonima&view=course&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="row">
		<div class="col-md-8">
			<fieldset class="options-form">
				<div class="control-group">
					<label class="control-label" for="jform_title"><?php echo Text::_('JGLOBAL_TITLE'); ?></label>
					<div class="controls">
						<input type="text" name="jform[title]" id="jform_title" class="form-control" required
							value="<?php echo $this->escape($item->title); ?>">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="jform_intro"><?php echo Text::_('COM_CYONIMA_INTRO'); ?></label>
					<div class="controls">
						<textarea name="jform[intro]" id="jform_intro" class="form-control" rows="3"><?php echo $this->escape($item->intro); ?></textarea>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="jform_description"><?php echo Text::_('COM_CYONIMA_DESCRIPTION'); ?></label>
					<div class="controls">
						<?php echo Editor::getInstance()->display('jform[description]', $item->description, '100%', '300px', 60, 20); ?>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="col-md-4">
			<fieldset class="options-form">
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
					<label class="control-label" for="jform_featured"><?php echo Text::_('COM_CYONIMA_FEATURED'); ?></label>
					<div class="controls">
						<select name="jform[featured]" id="jform_featured" class="form-select">
							<option value="0" <?php echo $item->featured == 0 ? 'selected' : ''; ?>><?php echo Text::_('JNO'); ?></option>
							<option value="1" <?php echo $item->featured == 1 ? 'selected' : ''; ?>><?php echo Text::_('JYES'); ?></option>
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="jform_image"><?php echo Text::_('COM_CYONIMA_IMAGE'); ?></label>
					<div class="controls">
						<input type="text" name="jform[image]" id="jform_image" class="form-control"
							value="<?php echo $this->escape($item->image); ?>"
							placeholder="images/com_cyonima/cover.jpg">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="jform_access"><?php echo Text::_('JFIELD_ACCESS_LABEL'); ?></label>
					<div class="controls">
						<?php echo HTMLHelper::_('access.assetgrouplist', 'jform[access]', $item->access, 'class="form-select"'); ?>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="jform_language"><?php echo Text::_('JFIELD_LANGUAGE_LABEL'); ?></label>
					<div class="controls">
						<?php echo HTMLHelper::_('select.genericlist', HTMLHelper::_('contentlanguage.existing', false, true), 'jform[language]', '', 'value', 'text', $item->language); ?>
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="jform[id]" value="<?php echo (int) $item->id; ?>">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
