<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$stats = $this->stats;
?>

<div class="cyonima-admin-dashboard">
	<div class="row">
		<div class="col-md-2">
			<div class="card">
				<div class="card-body text-center">
					<div class="h2"><?php echo $stats->courses; ?></div>
					<div><?php echo Text::_('COM_CYONIMA_COURSES'); ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card">
				<div class="card-body text-center">
					<div class="h2"><?php echo $stats->lessons; ?></div>
					<div><?php echo Text::_('COM_CYONIMA_LESSONS'); ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card">
				<div class="card-body text-center">
					<div class="h2"><?php echo $stats->enrollments; ?></div>
					<div><?php echo Text::_('COM_CYONIMA_ENROLLMENTS'); ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card">
				<div class="card-body text-center">
					<div class="h2"><?php echo $stats->exams; ?></div>
					<div><?php echo Text::_('COM_CYONIMA_EXAMS'); ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card">
				<div class="card-body text-center">
					<div class="h2"><?php echo $stats->certificates; ?></div>
					<div><?php echo Text::_('COM_CYONIMA_CERTIFICATES'); ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card">
				<div class="card-body text-center">
					<div class="h2"><?php echo $stats->paths; ?></div>
					<div><?php echo Text::_('COM_CYONIMA_LEARNING_PATHS'); ?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="mt-4">
		<a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_cyonima&view=courses'); ?>">
			<?php echo Text::_('COM_CYONIMA_MANAGE_COURSES'); ?>
		</a>
		<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_cyonima&view=enrollments'); ?>">
			<?php echo Text::_('COM_CYONIMA_MONITOR_ENROLLMENTS'); ?>
		</a>
	</div>
</div>
