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

$path = $this->path;
?>

<div class="cyonima cyonima-learningpath">
	<?php if (!$path) : ?>
		<p><?php echo Text::_('COM_CYONIMA_ERROR_LEARNING_PATH_NOT_FOUND'); ?></p>
		<?php return; ?>
	<?php endif; ?>

	<h1><?php echo $this->escape($path->title); ?></h1>

	<?php if ($path->description) : ?>
		<div><?php echo HTMLHelper::_('content.prepare', $path->description); ?></div>
	<?php endif; ?>

	<h2><?php echo Text::_('COM_CYONIMA_PATH_COURSES'); ?></h2>

	<ol class="cyonima-path-courses">
		<?php foreach ($this->courses as $course) : ?>
			<li>
				<a href="<?php echo Route::_('index.php?option=com_cyonima&view=course&id=' . (int) $course->id . ':' . $course->alias); ?>">
					<?php echo $this->escape($course->title); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ol>
</div>
