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

<form action="<?php echo Route::_('index.php?option=com_cyonima&view=courses'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<div class="js-stools">
					<div class="btn-toolbar">
						<div class="input-group">
							<input type="text" name="filter_search" id="filter_search" class="form-control"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>">
							<button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
							<button class="btn btn-secondary" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();">
								<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
							</button>
						</div>
					</div>
				</div>

				<table class="table table-striped" id="courseList">
					<thead>
						<tr>
							<th width="1%"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
							<th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
							<th width="10%"><?php echo Text::_('COM_CYONIMA_TEACHER'); ?></th>
							<th width="10%"><?php echo Text::_('JSTATUS'); ?></th>
							<th width="5%"><?php echo Text::_('COM_CYONIMA_HITS'); ?></th>
							<th width="5%"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<tr>
								<td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
								<td>
									<a href="<?php echo Route::_('index.php?option=com_cyonima&task=courses.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape($item->title); ?>
									</a>
									<div class="small">
										<a href="<?php echo Route::_('index.php?option=com_cyonima&view=lessons&filter_course=' . (int) $item->id); ?>"><?php echo Text::_('COM_CYONIMA_LESSONS'); ?></a>
										| <a href="<?php echo Route::_('index.php?option=com_cyonima&view=monitor&course_id=' . (int) $item->id); ?>"><?php echo Text::_('COM_CYONIMA_MONITOR'); ?></a>
									</div>
								</td>
								<td><?php echo $this->escape($item->teacher ?? ''); ?></td>
								<td><?php echo $item->published ? Text::_('JPUBLISHED') : Text::_('JUNPUBLISHED'); ?></td>
								<td><?php echo (int) $item->hits; ?></td>
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
