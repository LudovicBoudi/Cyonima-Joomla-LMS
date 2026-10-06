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

<div class="cyonima cyonima-learningpaths">
	<h1><?php echo Text::_('COM_CYONIMA_LEARNING_PATHS'); ?></h1>

	<?php if (empty($this->items)) : ?>
		<p><?php echo Text::_('COM_CYONIMA_NO_LEARNING_PATHS'); ?></p>
	<?php else : ?>
		<ul class="cyonima-paths">
			<?php foreach ($this->items as $item) : ?>
				<li>
					<a href="<?php echo Route::_('index.php?option=com_cyonima&view=learningpath&id=' . (int) $item->id . ':' . $item->alias); ?>">
						<?php echo $this->escape($item->title); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
