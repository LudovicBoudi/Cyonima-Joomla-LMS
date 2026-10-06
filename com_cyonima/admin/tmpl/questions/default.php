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

<form action="<?php echo Route::_('index.php?option=com_cyonima&view=questions'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<div class="js-stools">
					<div class="btn-toolbar">
						<div class="input-group">
							<input type="text" name="filter_exam" id="filter_exam" class="form-control"
								value="<?php echo $this->escape($this->state->get('filter.exam')); ?>"
								placeholder="<?php echo Text::_('COM_CYONIMA_EXAM'); ?>">
							<button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
							<button class="btn btn-secondary" type="button" onclick="document.getElementById('filter_exam').value='';this.form.submit();">
								<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
							</button>
						</div>
					</div>
				</div>

				<table class="table table-striped" id="questionList">
					<thead>
						<tr>
							<th width="1%"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
							<th><?php echo Text::_('COM_CYONIMA_QUESTION'); ?></th>
							<th width="10%"><?php echo Text::_('COM_CYONIMA_TYPE'); ?></th>
							<th width="10%"><?php echo Text::_('COM_CYONIMA_POINTS'); ?></th>
							<th width="5%"><?php echo Text::_('JGRID_HEADING_ORDERING'); ?></th>
							<th width="5%"><?php echo Text::_('JSTATUS'); ?></th>
							<th width="5%"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<tr>
								<td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
								<td>
									<a href="<?php echo Route::_('index.php?option=com_cyonima&task=questions.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape($item->question); ?>
									</a>
								</td>
								<td><?php echo $this->escape($item->type); ?></td>
								<td><?php echo (int) $item->points; ?></td>
								<td><?php echo (int) $item->ordering; ?></td>
								<td><?php echo $item->published ? Text::_('JPUBLISHED') : Text::_('JUNPUBLISHED'); ?></td>
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
