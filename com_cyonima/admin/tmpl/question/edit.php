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

$item   = $this->item;
$rows   = $item->optionRows ?? [];
$parent = $item->parent ?? '';
?>

<?php HTMLHelper::_('behavior.formvalidator'); ?>
<form action="<?php echo Route::_('index.php?option=com_cyonima&view=question&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset class="options-form">
		<div class="control-group">
			<label class="control-label" for="jform_parent"><?php echo Text::_('COM_CYONIMA_QUESTION_PARENT'); ?></label>
			<div class="controls">
				<select name="jform[parent]" id="jform_parent" class="form-select" required>
					<option value=""><?php echo Text::_('COM_CYONIMA_QUESTION_NO_PARENT'); ?></option>
					<?php if ($this->exams) : ?>
						<optgroup label="<?php echo Text::_('COM_CYONIMA_EXAMS'); ?>">
							<?php foreach ($this->exams as $exam) : ?>
								<option value="exam:<?php echo (int) $exam->id; ?>"
									<?php echo $parent === 'exam:' . $exam->id ? 'selected' : ''; ?>>
									<?php echo $this->escape($exam->title); ?>
								</option>
							<?php endforeach; ?>
						</optgroup>
					<?php endif; ?>
					<?php if ($this->assignments) : ?>
						<optgroup label="<?php echo Text::_('COM_CYONIMA_ASSIGNMENTS'); ?>">
							<?php foreach ($this->assignments as $assignment) : ?>
								<option value="assignment:<?php echo (int) $assignment->id; ?>"
									<?php echo $parent === 'assignment:' . $assignment->id ? 'selected' : ''; ?>>
									<?php echo $this->escape($assignment->title); ?>
								</option>
							<?php endforeach; ?>
						</optgroup>
					<?php endif; ?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_question"><?php echo Text::_('COM_CYONIMA_QUESTION'); ?></label>
			<div class="controls">
				<textarea name="jform[question]" id="jform_question" class="form-control" rows="3" required><?php echo $this->escape($item->question ?? ''); ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_type"><?php echo Text::_('COM_CYONIMA_TYPE'); ?></label>
			<div class="controls">
				<select name="jform[type]" id="jform_type" class="form-select">
					<option value="single" <?php echo $item->type === 'single' ? 'selected' : ''; ?>><?php echo Text::_('COM_CYONIMA_TYPE_SINGLE'); ?></option>
					<option value="multiple" <?php echo $item->type === 'multiple' ? 'selected' : ''; ?>><?php echo Text::_('COM_CYONIMA_TYPE_MULTIPLE'); ?></option>
					<option value="truefalse" <?php echo $item->type === 'truefalse' ? 'selected' : ''; ?>><?php echo Text::_('COM_CYONIMA_TYPE_TRUEFALSE'); ?></option>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="question-options"><?php echo Text::_('COM_CYONIMA_OPTIONS'); ?></label>
			<div class="controls">
				<p class="form-control-plaintext cyonima-muted"><?php echo Text::_('COM_CYONIMA_MARK_CORRECT_ANSWER'); ?></p>

				<div id="question-options" class="cyonima-options-list"
					data-yes="<?php echo $this->escape(Text::_('JYES')); ?>"
					data-no="<?php echo $this->escape(Text::_('JNO')); ?>"
					data-label-correct="<?php echo $this->escape(Text::_('COM_CYONIMA_CORRECT')); ?>"
					data-label-remove="<?php echo $this->escape(Text::_('COM_CYONIMA_REMOVE_OPTION')); ?>">
					<?php foreach ($rows as $i => $row) : ?>
						<div class="question-option input-group mb-2">
							<input type="text" name="jform[options][<?php echo (int) $i; ?>][text]" class="form-control"
								value="<?php echo $this->escape($row['text']); ?>"
								placeholder="<?php echo Text::sprintf('COM_CYONIMA_OPTION_NUMBER', (int) $i + 1); ?>">
							<label class="input-group-text">
								<input type="checkbox" name="jform[correct][<?php echo (int) $i; ?>]" value="1" class="me-1"
									<?php echo $row['correct'] ? 'checked' : ''; ?>>
								<?php echo Text::_('COM_CYONIMA_CORRECT'); ?>
							</label>
							<button type="button" class="btn btn-outline-danger question-option-remove" title="<?php echo Text::_('COM_CYONIMA_REMOVE_OPTION'); ?>">
								<span class="icon-times" aria-hidden="true"></span>
							</button>
						</div>
					<?php endforeach; ?>
				</div>

				<button type="button" class="btn btn-secondary btn-sm" id="cyonima-add-option">
					<?php echo Text::_('COM_CYONIMA_ADD_OPTION'); ?>
				</button>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="jform_points"><?php echo Text::_('COM_CYONIMA_POINTS'); ?></label>
			<div class="controls">
				<input type="number" min="1" name="jform[points]" id="jform_points" class="form-control"
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

<script>
(function () {
	'use strict';

	var container = document.getElementById('question-options');
	var addButton = document.getElementById('cyonima-add-option');
	var typeField = document.getElementById('jform_type');

	if (!container || !addButton || !typeField) {
		return;
	}

	var minRows = 2;

	function rows() {
		return Array.prototype.slice.call(container.querySelectorAll('.question-option'));
	}

	function renumber() {
		rows().forEach(function (row, i) {
			var text = row.querySelector('input[type="text"]');
			var box = row.querySelector('input[type="checkbox"]');

			text.name = 'jform[options][' + i + '][text]';
			box.name = 'jform[correct][' + i + ']';
		});
	}

	function makeRow(text, checked) {
		var row = document.createElement('div');
		row.className = 'question-option input-group mb-2';

		var input = document.createElement('input');
		input.type = 'text';
		input.className = 'form-control';
		input.value = text || '';

		var label = document.createElement('label');
		label.className = 'input-group-text';

		var box = document.createElement('input');
		box.type = 'checkbox';
		box.value = '1';
		box.className = 'me-1';
		box.checked = !!checked;

		label.appendChild(box);
		label.appendChild(document.createTextNode(' ' + container.dataset.labelCorrect));

		var remove = document.createElement('button');
		remove.type = 'button';
		remove.className = 'btn btn-outline-danger question-option-remove';
		remove.title = container.dataset.labelRemove;
		remove.innerHTML = '<span class="icon-times" aria-hidden="true"></span>';

		row.appendChild(input);
		row.appendChild(label);
		row.appendChild(remove);

		return row;
	}

	function enforceSingle() {
		if (typeField.value === 'multiple') {
			return;
		}

		var last = null;

		rows().forEach(function (row) {
			var box = row.querySelector('input[type="checkbox"]');

			if (box.checked) {
				last = box;
			}
		});

		rows().forEach(function (row) {
			var box = row.querySelector('input[type="checkbox"]');

			if (box !== last) {
				box.checked = false;
			}
		});
	}

	addButton.addEventListener('click', function () {
		container.appendChild(makeRow('', false));
		renumber();
	});

	container.addEventListener('click', function (event) {
		var button = event.target.closest('.question-option-remove');

		if (!button) {
			return;
		}

		event.preventDefault();

		if (rows().length <= minRows) {
			return;
		}

		button.closest('.question-option').remove();
		renumber();
	});

	container.addEventListener('change', function (event) {
		if (event.target.type === 'checkbox') {
			enforceSingle();
		}
	});

	typeField.addEventListener('change', function () {
		if (typeField.value === 'truefalse') {
			var list = rows();

			var empty = list.every(function (row) {
				return row.querySelector('input[type="text"]').value === '';
			});

			if (empty && list.length >= 2) {
				list[0].querySelector('input[type="text"]').value = container.dataset.yes;
				list[1].querySelector('input[type="text"]').value = container.dataset.no;
			}
		}

		enforceSingle();
	});

	renumber();
})();
</script>
