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

<form action="<?php echo Route::_('index.php?option=com_cyonima&view=submissions'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<div class="js-stools">
					<div class="btn-toolbar">
						<div class="input-group">
							<input type="text" name="filter_assignment" id="filter_assignment" class="form-control"
								value="<?php echo $this->escape($this->state->get('filter.assignment')); ?>"
								placeholder="<?php echo Text::_('COM_CYONIMA_ASSIGNMENT'); ?>">
							<button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
							<button class="btn btn-secondary" type="button" onclick="document.getElementById('filter_assignment').value='';this.form.submit();">
								<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
							</button>
						</div>
					</div>
				</div>

				<table class="table table-striped" id="submissionList">
					<thead>
						<tr>
							<th width="1%"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
							<th width="15%"><?php echo Text::_('COM_CYONIMA_STUDENT'); ?></th>
							<th><?php echo Text::_('COM_CYONIMA_ASSIGNMENT'); ?></th>
							<th width="15%"><?php echo Text::_('COM_CYONIMA_SUBMITTED_DATE'); ?></th>
							<th width="10%"><?php echo Text::_('JSTATUS'); ?></th>
							<th width="10%"><?php echo Text::_('COM_CYONIMA_SCORE'); ?></th>
							<th width="5%"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<tr>
								<td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
								<td><?php echo $this->escape($item->student); ?></td>
								<td><?php echo $this->escape($item->assignment_title); ?></td>
								<td><?php echo $this->escape($item->submitted_date); ?></td>
								<td><?php echo $this->escape($item->status); ?></td>
								<td><?php echo $this->escape($item->score); ?> / <?php echo (int) $item->max_score; ?></td>
								<td><?php echo (int) $item->id; ?></td>
							</tr>
							<tr>
								<td colspan="7">
									<form action="<?php echo Route::_('index.php?option=com_cyonima&task=submissions.grade&id=' . (int) $item->id); ?>" method="post" class="form-inline">
										<input type="text" name="score" class="form-control" value="<?php echo $this->escape($item->score); ?>" placeholder="<?php echo Text::_('COM_CYONIMA_SCORE'); ?>">
										<input type="text" name="feedback" class="form-control" value="" placeholder="<?php echo Text::_('COM_CYONIMA_FEEDBACK'); ?>">
										<input type="hidden" name="task" value="submissions.grade">
										<input type="hidden" name="id" value="<?php echo (int) $item->id; ?>">
										<button class="btn btn-primary btn-sm" type="submit"><?php echo Text::_('COM_CYONIMA_SAVE_GRADE'); ?></button>
										<?php echo HTMLHelper::_('form.token'); ?>
									</form>
								</td>
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
