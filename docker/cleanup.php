<?php

const _JEXEC = 1;
const JOOMLA_MINIMUM_PHP = '8.1.0';

define('JPATH_BASE', '/var/www/html');

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;

$db = Factory::getContainer()->get('DatabaseDriver');
$prefix = $db->getPrefix();

$tables = [
    'cyonima_learning_path_courses', 'cyonima_learning_paths', 'cyonima_certificates',
    'cyonima_certificate_templates', 'cyonima_exam_attempts', 'cyonima_questions',
    'cyonima_exams', 'cyonima_submissions', 'cyonima_assignments', 'cyonima_lesson_progress',
    'cyonima_enrollments', 'cyonima_lessons', 'cyonima_courses',
];

foreach ($tables as $t) {
    $db->setQuery('DROP TABLE IF EXISTS ' . $db->quoteName($prefix . $t))->execute();
}

$db->setQuery('DELETE FROM ' . $db->quoteName('#__extensions') . ' WHERE element = ' . $db->quote('com_cyonima') . ' AND type = ' . $db->quote('component'))->execute();
$db->setQuery('DELETE FROM ' . $db->quoteName('#__assets') . ' WHERE name = ' . $db->quote('com_cyonima'))->execute();
$db->setQuery('DELETE FROM ' . $db->quoteName('#__menu') . ' WHERE link LIKE ' . $db->quote('%com_cyonima%'))->execute();

foreach ([
    JPATH_ADMINISTRATOR . '/components/com_cyonima',
    JPATH_SITE . '/components/com_cyonima',
    JPATH_API . '/components/com_cyonima',
    JPATH_ROOT . '/media/com_cyonima',
] as $dir) {
    if (is_dir($dir)) {
        Folder::delete($dir);
    }
}

// Stale legacy language files take precedence over the extension language folder
// and would mask freshly installed strings. Remove them so the dispatcher falls
// back to the files shipped inside the component.
foreach ([
    JPATH_SITE . '/language/en-GB/com_cyonima.ini',
    JPATH_SITE . '/language/en-GB/com_cyonima.sys.ini',
    JPATH_ADMINISTRATOR . '/language/en-GB/com_cyonima.ini',
    JPATH_ADMINISTRATOR . '/language/en-GB/com_cyonima.sys.ini',
] as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

echo "cleaned\n";
