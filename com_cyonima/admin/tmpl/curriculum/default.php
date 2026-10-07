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

$course   = $this->course;
$courseId = (int) ($course->id ?? 0);

$saveUrl = Route::_('index.php?option=com_cyonima&task=curriculum.sectionSave');
$addUrl  = Route::_('index.php?option=com_cyonima&task=curriculum.itemAdd');

$confirm = htmlspecialchars(json_encode(Text::_('COM_CYONIMA_CONFIRM_DELETE')), ENT_QUOTES);

$typeLabels = [
	'content'    => Text::_('COM_CYONIMA_LESSON_TYPE_CONTENT'),
	'video'      => Text::_('COM_CYONIMA_LESSON_TYPE_VIDEO'),
	'pdf'        => Text::_('COM_CYONIMA_LESSON_TYPE_PDF'),
	'link'       => Text::_('COM_CYONIMA_LESSON_TYPE_LINK'),
	'assignment' => Text::_('COM_CYONIMA_LESSON_TYPE_ASSIGNMENT'),
	'exam'       => Text::_('COM_CYONIMA_LESSON_TYPE_EXAM'),
];

$typeBadges = [
	'content'    => 'bg-secondary',
	'video'      => 'bg-secondary',
	'pdf'        => 'bg-secondary',
	'link'       => 'bg-secondary',
	'assignment' => 'bg-info text-dark',
	'exam'       => 'bg-primary',
];

$renderItems = function (array $items) use ($courseId, $typeLabels, $typeBadges, $confirm) {
	?>
	<table class="table table-hover mb-0">
		<thead>
			<tr>
				<th width="12%"><?php echo Text::_('COM_CYONIMA_TYPE'); ?></th>
				<th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
				<th width="40%" class="text-end"><?php echo Text::_('COM_CYONIMA_ACTIONS'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($items as $item) : ?>
				<?php
					$label     = $typeLabels[$item->type] ?? $item->type;
					$badge     = $typeBadges[$item->type] ?? 'bg-secondary';
					$linked    = (bool) $item->linked_id;
					$editUrl   = Route::_(
						'index.php?option=com_cyonima&task='
						. ($linked ? $item->type . '.edit' : 'lessons.edit') . '&id=' . (int) ($linked ? $item->linked_id : $item->id)
					);
					$questionsUrl = $linked
						? Route::_('index.php?option=com_cyonima&view=questions&filter_' . $item->type . '=' . (int) $item->linked_id)
						: '';
				?>
				<tr>
					<td><span class="badge <?php echo $badge; ?>"><?php echo $this->escape($label); ?></span></td>
					<td>
						<a href="<?php echo $editUrl; ?>"><?php echo $this->escape($item->title); ?></a>
						<?php if ($linked) : ?>
							<span class="small text-muted">
								<?php echo (int) $item->question_count; ?>
								<?php echo (int) $item->question_count === 1 ? Text::_('COM_CYONIMA_QUESTION') : Text::_('COM_CYONIMA_QUESTIONS'); ?>
							</span>
						<?php endif; ?>
						<?php if (in_array($item->type, ['assignment', 'exam'], true) && !$linked) : ?>
							<span class="badge bg-warning text-dark"><?php echo Text::_('COM_CYONIMA_ITEM_NOT_LINKED'); ?></span>
						<?php endif; ?>
						<?php if (!$item->published) : ?>
							<span class="badge bg-warning text-dark"><?php echo Text::_('JUNPUBLISHED'); ?></span>
						<?php endif; ?>
					</td>
					<td class="text-end">
						<a class="btn btn-sm btn-outline-secondary" href="<?php echo $editUrl; ?>">
							<?php echo Text::_('JEDIT'); ?>
						</a>
						<?php if ($linked) : ?>
							<a class="btn btn-sm btn-outline-secondary" href="<?php echo $questionsUrl; ?>">
								<?php echo Text::_('COM_CYONIMA_QUESTIONS'); ?>
							</a>
						<?php endif; ?>
						<form class="d-inline" action="<?php echo Route::_('index.php?option=com_cyonima&task=curriculum.itemMove'); ?>" method="post">
							<input type="hidden" name="id" value="<?php echo (int) $item->id; ?>">
							<input type="hidden" name="course_id" value="<?php echo (int) $courseId; ?>">
							<input type="hidden" name="delta" value="-1">
							<?php echo HTMLHelper::_('form.token'); ?>
							<button class="btn btn-sm btn-outline-secondary" type="submit"><?php echo Text::_('COM_CYONIMA_MOVE_UP'); ?></button>
						</form>
						<form class="d-inline" action="<?php echo Route::_('index.php?option=com_cyonima&task=curriculum.itemMove'); ?>" method="post">
							<input type="hidden" name="id" value="<?php echo (int) $item->id; ?>">
							<input type="hidden" name="course_id" value="<?php echo (int) $courseId; ?>">
							<input type="hidden" name="delta" value="1">
							<?php echo HTMLHelper::_('form.token'); ?>
							<button class="btn btn-sm btn-outline-secondary" type="submit"><?php echo Text::_('COM_CYONIMA_MOVE_DOWN'); ?></button>
						</form>
						<form class="d-inline" action="<?php echo Route::_('index.php?option=com_cyonima&task=curriculum.itemDelete'); ?>" method="post">
							<input type="hidden" name="id" value="<?php echo (int) $item->id; ?>">
							<input type="hidden" name="course_id" value="<?php echo (int) $courseId; ?>">
							<?php echo HTMLHelper::_('form.token'); ?>
							<button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm(<?php echo $confirm; ?>);">
								<?php echo Text::_('JDELETE'); ?>
							</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
};
?>

<div class="row">
	<div class="col-md-12">
		<div id="j-main-container" class="j-main-container">
			<?php if (empty($course)) : ?>
				<div class="alert alert-warning"><?php echo Text::_('COM_CYONIMA_ERROR_COURSE_NOT_FOUND'); ?></div>
			<?php else : ?>
				<p class="d-flex flex-wrap gap-2">
					<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_cyonima&view=course&layout=edit&id=' . $courseId); ?>">
						<?php echo Text::_('COM_CYONIMA_BACK_TO_COURSE'); ?>
					</a>
					<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_cyonima&view=lessons&filter_course=' . $courseId); ?>">
						<?php echo Text::_('COM_CYONIMA_MANAGE_LESSONS'); ?>
					</a>
				</p>

				<h2><?php echo $this->escape($course->title); ?></h2>
				<h3><?php echo Text::_('COM_CYONIMA_SECTIONS'); ?></h3>

				<div class="card mb-3">
					<div class="card-body">
						<form action="<?php echo $saveUrl; ?>" method="post" class="d-flex gap-2 flex-wrap">
							<label class="visually-hidden" for="new_section_title"><?php echo Text::_('COM_CYONIMA_SECTION_TITLE'); ?></label>
							<input class="form-control" style="max-width: 480px" type="text" id="new_section_title"
								name="jform[title]" placeholder="<?php echo Text::_('COM_CYONIMA_SECTION_TITLE'); ?>" required>
							<input type="hidden" name="course_id" value="<?php echo (int) $courseId; ?>">
							<?php echo HTMLHelper::_('form.token'); ?>
							<button class="btn btn-primary" type="submit"><?php echo Text::_('COM_CYONIMA_ADD_SECTION'); ?></button>
						</form>
					</div>
				</div>

				<?php if (!empty($this->looseItems)) : ?>
					<div class="card mb-3">
						<div class="card-header"><strong><?php echo Text::_('COM_CYONIMA_NO_SECTION'); ?></strong></div>
						<div class="card-body p-0">
							<?php $renderItems($this->looseItems); ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if (empty($this->sections)) : ?>
					<p class="text-muted"><?php echo Text::_('COM_CYONIMA_NO_SECTIONS'); ?></p>
				<?php endif; ?>

				<?php foreach ($this->sections as $section) : ?>
					<?php $sectionId = (int) $section->id; ?>
					<div class="card mb-3">
						<div class="card-header d-flex align-items-center gap-2 flex-wrap">
							<form class="d-flex gap-2 flex-grow-1" style="min-width: 260px" action="<?php echo $saveUrl; ?>" method="post">
								<label class="visually-hidden" for="section_title_<?php echo $sectionId; ?>">
									<?php echo Text::_('COM_CYONIMA_SECTION_TITLE'); ?>
								</label>
								<input class="form-control form-control-sm" type="text" id="section_title_<?php echo $sectionId; ?>"
									name="jform[title]" value="<?php echo $this->escape($section->title); ?>" required>
								<input type="hidden" name="jform[id]" value="<?php echo $sectionId; ?>">
								<input type="hidden" name="course_id" value="<?php echo (int) $courseId; ?>">
								<?php echo HTMLHelper::_('form.token'); ?>
								<button class="btn btn-sm btn-outline-primary" type="submit"><?php echo Text::_('JSAVE'); ?></button>
							</form>
							<form class="d-inline" action="<?php echo Route::_('index.php?option=com_cyonima&task=curriculum.sectionMove'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo $sectionId; ?>">
								<input type="hidden" name="course_id" value="<?php echo (int) $courseId; ?>">
								<input type="hidden" name="delta" value="-1">
								<?php echo HTMLHelper::_('form.token'); ?>
								<button class="btn btn-sm btn-outline-secondary" type="submit"><?php echo Text::_('COM_CYONIMA_MOVE_UP'); ?></button>
							</form>
							<form class="d-inline" action="<?php echo Route::_('index.php?option=com_cyonima&task=curriculum.sectionMove'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo $sectionId; ?>">
								<input type="hidden" name="course_id" value="<?php echo (int) $courseId; ?>">
								<input type="hidden" name="delta" value="1">
								<?php echo HTMLHelper::_('form.token'); ?>
								<button class="btn btn-sm btn-outline-secondary" type="submit"><?php echo Text::_('COM_CYONIMA_MOVE_DOWN'); ?></button>
							</form>
							<form class="d-inline" action="<?php echo Route::_('index.php?option=com_cyonima&task=curriculum.sectionDelete'); ?>" method="post">
								<input type="hidden" name="id" value="<?php echo $sectionId; ?>">
								<input type="hidden" name="course_id" value="<?php echo (int) $courseId; ?>">
								<?php echo HTMLHelper::_('form.token'); ?>
								<button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm(<?php echo $confirm; ?>);">
									<?php echo Text::_('JDELETE'); ?>
								</button>
							</form>
						</div>
						<div class="card-body p-0">
							<?php if (empty($section->items)) : ?>
								<p class="text-muted p-3 mb-0"><?php echo Text::_('COM_CYONIMA_NO_ITEMS'); ?></p>
							<?php else : ?>
								<?php $renderItems($section->items); ?>
							<?php endif; ?>
						</div>
						<div class="card-footer">
							<form action="<?php echo $addUrl; ?>" method="post" class="d-flex gap-2 flex-wrap">
								<label class="visually-hidden" for="item_title_<?php echo $sectionId; ?>">
									<?php echo Text::_('JGLOBAL_TITLE'); ?>
								</label>
								<input class="form-control" style="max-width: 320px" type="text" id="item_title_<?php echo $sectionId; ?>"
									name="jform[title]" placeholder="<?php echo Text::_('JGLOBAL_TITLE'); ?>" required>
								<label class="visually-hidden" for="item_type_<?php echo $sectionId; ?>">
									<?php echo Text::_('COM_CYONIMA_TYPE'); ?>
								</label>
								<select class="form-select" style="max-width: 220px" id="item_type_<?php echo $sectionId; ?>" name="jform[type]">
									<?php foreach ($typeLabels as $value => $text) : ?>
										<option value="<?php echo $value; ?>"><?php echo $this->escape($text); ?></option>
									<?php endforeach; ?>
								</select>
								<input type="hidden" name="section_id" value="<?php echo $sectionId; ?>">
								<input type="hidden" name="course_id" value="<?php echo (int) $courseId; ?>">
								<?php echo HTMLHelper::_('form.token'); ?>
								<button class="btn btn-primary" type="submit"><?php echo Text::_('COM_CYONIMA_ADD_ITEM'); ?></button>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
