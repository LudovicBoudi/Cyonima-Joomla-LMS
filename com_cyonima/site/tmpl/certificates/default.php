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

<div class="cyonima cyonima-certificates">
	<h1><?php echo Text::_('COM_CYONIMA_MY_CERTIFICATES'); ?></h1>

	<?php if (empty($this->items)) : ?>
		<p><?php echo Text::_('COM_CYONIMA_NO_CERTIFICATES'); ?></p>
	<?php else : ?>
		<table class="table">
			<thead>
				<tr>
					<th><?php echo Text::_('COM_CYONIMA_COURSE'); ?></th>
					<th><?php echo Text::_('COM_CYONIMA_CERTIFICATE_NUMBER'); ?></th>
					<th><?php echo Text::_('COM_CYONIMA_ISSUED_DATE'); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->items as $item) : ?>
					<tr>
						<td><?php echo $this->escape($item->course_title); ?></td>
						<td><?php echo $this->escape($item->certificate_number); ?></td>
						<td><?php echo $this->escape($item->issued_date); ?></td>
						<td>
							<a class="btn btn-sm btn-primary" href="<?php echo Route::_('index.php?option=com_cyonima&task=certificate.download&id=' . (int) $item->id); ?>">
								<?php echo Text::_('COM_CYONIMA_DOWNLOAD'); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
