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

HTMLHelper::_('behavior.multiselect');
?>

<form action="<?php echo Route::_('index.php?option=com_cyonima&view=enrollments'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<div class="js-stools">
					<div class="btn-toolbar">
						<div class="input-group">
							<input type="text" name="filter_search" id="filter_search" class="form-control"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>">
							<input type="text" name="filter_course" id="filter_course" class="form-control"
								value="<?php echo $this->escape($this->state->get('filter.course')); ?>"
								placeholder="<?php echo Text::_('COM_CYONIMA_COURSE'); ?>">
							<button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
							<button class="btn btn-secondary" type="button" onclick="document.getElementById('filter_search').value='';document.getElementById('filter_course').value='';this.form.submit();">
								<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
							</button>
						</div>
					</div>
				</div>

				<table class="table table-striped" id="enrollmentList">
					<thead>
						<tr>
							<th width="1%"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
							<th><?php echo Text::_('COM_CYONIMA_STUDENT'); ?></th>
							<th><?php echo Text::_('COM_CYONIMA_COURSE'); ?></th>
							<th width="10%"><?php echo Text::_('JSTATUS'); ?></th>
							<th width="10%"><?php echo Text::_('COM_CYONIMA_PROGRESS'); ?></th>
							<th width="15%"><?php echo Text::_('COM_CYONIMA_ENROLLED'); ?></th>
							<th width="15%"><?php echo Text::_('COM_CYONIMA_COMPLETED'); ?></th>
							<th width="5%"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<tr>
								<td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
								<td><?php echo $this->escape($item->student); ?></td>
								<td><?php echo $this->escape($item->course_title); ?></td>
								<td><?php echo $this->escape($item->status); ?></td>
								<td><?php echo (int) $item->progress; ?>%</td>
								<td><?php echo $this->escape($item->enrolled_date); ?></td>
								<td><?php echo $this->escape($item->completed_date); ?></td>
								<td><?php echo (int) $item->id; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php echo $this->pagination->getListFooter(); ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<input type="hidden" name="boxchecked" value="0">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
