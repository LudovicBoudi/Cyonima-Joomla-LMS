<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$course   = $this->course;
$students = $this->students;
$results  = $this->results;
?>

<div class="row">
	<div class="col-md-12">
		<div id="j-main-container" class="j-main-container">
			<p>
				<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_cyonima&view=courses'); ?>">
					<?php echo Text::_('COM_CYONIMA_BACK_TO_COURSES'); ?>
				</a>
			</p>

			<h2><?php echo $this->escape($course->title ?? ''); ?></h2>

			<h3><?php echo Text::_('COM_CYONIMA_STUDENTS'); ?></h3>
			<table class="table table-striped" id="monitorStudentList">
				<thead>
					<tr>
						<th><?php echo Text::_('COM_CYONIMA_STUDENT'); ?></th>
						<th><?php echo Text::_('COM_CYONIMA_EMAIL'); ?></th>
						<th width="10%"><?php echo Text::_('COM_CYONIMA_PROGRESS'); ?></th>
						<th width="10%"><?php echo Text::_('JSTATUS'); ?></th>
						<th width="15%"><?php echo Text::_('COM_CYONIMA_ENROLLED'); ?></th>
						<th width="15%"><?php echo Text::_('COM_CYONIMA_COMPLETED'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($students as $student) : ?>
						<tr>
							<td><?php echo $this->escape($student->student); ?></td>
							<td><?php echo $this->escape($student->email); ?></td>
							<td><?php echo (int) $student->progress; ?>%</td>
							<td><?php echo $this->escape($student->status); ?></td>
							<td><?php echo $this->escape($student->enrolled_date); ?></td>
							<td><?php echo $this->escape($student->completed_date); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h3><?php echo Text::_('COM_CYONIMA_RESULTS'); ?></h3>

			<h4><?php echo Text::_('COM_CYONIMA_ASSIGNMENTS'); ?></h4>
			<table class="table table-striped" id="monitorAssignmentResults">
				<thead>
					<tr>
						<th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
						<th width="10%"><?php echo Text::_('COM_CYONIMA_SCORE'); ?></th>
						<th width="10%"><?php echo Text::_('COM_CYONIMA_MAX_SCORE'); ?></th>
						<th width="10%"><?php echo Text::_('JSTATUS'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($results['assignments'] as $assignment) : ?>
						<tr>
							<td><?php echo $this->escape($assignment->title); ?></td>
							<td><?php echo $this->escape($assignment->score); ?></td>
							<td><?php echo $this->escape($assignment->max_score); ?></td>
							<td><?php echo $this->escape($assignment->status); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h4><?php echo Text::_('COM_CYONIMA_EXAMS'); ?></h4>
			<table class="table table-striped" id="monitorExamResults">
				<thead>
					<tr>
						<th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
						<th width="10%"><?php echo Text::_('COM_CYONIMA_SCORE'); ?></th>
						<th width="10%"><?php echo Text::_('COM_CYONIMA_MAX_SCORE'); ?></th>
						<th width="10%"><?php echo Text::_('COM_CYONIMA_PASSED'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($results['exams'] as $exam) : ?>
						<tr>
							<td><?php echo $this->escape($exam->title); ?></td>
							<td><?php echo $this->escape($exam->score); ?></td>
							<td><?php echo $this->escape($exam->max_score); ?></td>
							<td><?php echo $exam->passed ? Text::_('JYES') : Text::_('JNO'); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
