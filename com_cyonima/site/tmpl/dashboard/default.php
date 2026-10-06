<?php

/**
 * @package     Cyonima\Component\Cyonima\Site
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('stylesheet', 'com_cyonima/cyonima.css', ['version' => 'auto', 'relative' => true]);
?>

<div class="cyonima cyonima-dashboard">
	<h1><?php echo Text::_('COM_CYONIMA_MY_COURSES'); ?></h1>

	<?php if (empty($this->items)) : ?>
		<p><?php echo Text::_('COM_CYONIMA_NO_ENROLLMENTS'); ?></p>
	<?php else : ?>
		<table class="table">
			<thead>
				<tr>
					<th><?php echo Text::_('COM_CYONIMA_COURSE'); ?></th>
					<th><?php echo Text::_('COM_CYONIMA_STATUS'); ?></th>
					<th><?php echo Text::_('COM_CYONIMA_PROGRESS'); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->items as $item) : ?>
					<tr>
						<td>
							<a href="<?php echo Route::_('index.php?option=com_cyonima&view=course&id=' . (int) $item->course_id . ':' . $item->alias); ?>">
								<?php echo $this->escape($item->title); ?>
							</a>
						</td>
						<td><?php echo $this->escape($item->status); ?></td>
						<td>
							<div class="progress">
								<div class="progress-bar" style="width:<?php echo (int) $item->progress; ?>%"></div>
							</div>
							<?php echo (int) $item->progress; ?>%
						</td>
						<td>
							<?php if ($item->status === 'completed') : ?>
								<a class="btn btn-sm btn-success" href="<?php echo Route::_('index.php?option=com_cyonima&view=certificates'); ?>">
									<?php echo Text::_('COM_CYONIMA_CERTIFICATE'); ?>
								</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
