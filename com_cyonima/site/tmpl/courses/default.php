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

<div class="cyonima cyonima-courses">
	<h1><?php echo Text::_('COM_CYONIMA_COURSES_TITLE'); ?></h1>

	<?php if (empty($this->items)) : ?>
		<p><?php echo Text::_('COM_CYONIMA_NO_COURSES'); ?></p>
	<?php else : ?>
		<div class="cyonima-grid">
			<?php foreach ($this->items as $item) : ?>
				<?php $link = Route::_('index.php?option=com_cyonima&view=course&id=' . (int) $item->id . ':' . $item->alias); ?>
				<div class="cyonima-card">
					<?php if ($item->image) : ?>
						<a href="<?php echo $link; ?>" class="cyonima-card__image">
							<img src="<?php echo $this->escape($item->image); ?>" alt="<?php echo $this->escape($item->title); ?>">
						</a>
					<?php endif; ?>
					<div class="cyonima-card__body">
						<h3>
							<a href="<?php echo $link; ?>"><?php echo $this->escape($item->title); ?></a>
						</h3>
						<?php if ($item->intro) : ?>
							<p><?php echo $this->escape($item->intro); ?></p>
						<?php endif; ?>
						<?php if ($item->teacher) : ?>
							<div class="cyonima-muted"><?php echo Text::sprintf('COM_CYONIMA_BY_TEACHER', $this->escape($item->teacher)); ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php echo $this->pagination->getPagesLinks(); ?>
	<?php endif; ?>
</div>
