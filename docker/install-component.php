<?php

/**
 * Install com_cyonima from the mounted source and verify it.
 *
 * Run inside the Joomla container:
 *   php /cyonima/docker/install-component.php
 */

const _JEXEC = 1;
const JOOMLA_MINIMUM_PHP = '8.1.0';

define('JPATH_BASE', '/var/www/html');

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;

// Boot the DI container with the CLI session backend (mirrors cli/joomla.php).
$container = Factory::getContainer();

$container->alias('session', 'session.cli')
    ->alias('JSession', 'session.cli')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.cli')
    ->alias(\Joomla\Session\Session::class, 'session.cli')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.cli');

$app                  = $container->get(\Joomla\Console\Application::class);
Factory::$application = $app;

// Load the extension namespace map (mirrors ConsoleApplication::execute()).
$app->createExtensionNamespaceMap();

$source = '/cyonima/com_cyonima';

if (!is_file($source . '/cyonima.xml')) {
    fwrite(STDERR, "Manifest not found at $source\n");
    exit(1);
}

echo "Installing com_cyonima from $source ...\n";

$db = $container->get('DatabaseDriver');

// The namespaced "extension" plugins (finder, joomla, joomlaupdate) are not
// CLI-autoloadable and break the onExtensionAfterInstall event. Disable them
// for the install, then restore. The "namespacemap" plugin is kept enabled so
// that our component namespace gets registered.
$disabled = $db->setQuery(
    $db->getQuery(true)
        ->select($db->quoteName(['extension_id', 'element', 'enabled']))
        ->from($db->quoteName('#__extensions'))
        ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
        ->where($db->quoteName('folder') . ' = ' . $db->quote('extension'))
        ->where($db->quoteName('element') . ' <> ' . $db->quote('namespacemap'))
)->loadObjectList();

foreach ($disabled as $plugin) {
    $db->setQuery(
        $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . ' = 0')
            ->where($db->quoteName('extension_id') . ' = ' . (int) $plugin->extension_id)
    )->execute();
}

try {
    $installer = new Installer();

    if (!$installer->install($source)) {
        fwrite(STDERR, "Install failed: " . $installer->getError() . "\n");
        exit(1);
    }
} catch (\Throwable $e) {
    fwrite(STDERR, 'EXCEPTION: ' . get_class($e) . ': ' . $e->getMessage() . "\n");
    exit(1);
}

// Restore the plugin states.
foreach ($disabled as $plugin) {
    $db->setQuery(
        $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . ' = ' . (int) $plugin->enabled)
            ->where($db->quoteName('extension_id') . ' = ' . (int) $plugin->extension_id)
    )->execute();
}

echo "Component installed.\n";

// Verification.

$tables = [
    '#__cyonima_courses',
    '#__cyonima_lessons',
    '#__cyonima_sections',
    '#__cyonima_enrollments',
    '#__cyonima_lesson_progress',
    '#__cyonima_assignments',
    '#__cyonima_submissions',
    '#__cyonima_exams',
    '#__cyonima_questions',
    '#__cyonima_exam_attempts',
    '#__cyonima_certificate_templates',
    '#__cyonima_certificates',
    '#__cyonima_learning_paths',
    '#__cyonima_learning_path_courses',
];

$allTables = array_map('strtolower', $db->getTableList());
$missing   = [];

foreach ($tables as $table) {
    $real = str_replace('#__', $db->getPrefix(), $table);

    if (!in_array(strtolower($real), $allTables, true)) {
        $missing[] = $real;
    }
}

echo $missing ? "MISSING TABLES: " . implode(', ', $missing) . "\n" : "All 14 tables present.\n";

$groups = $db->setQuery(
    $db->getQuery(true)
        ->select($db->quoteName('title'))
        ->from($db->quoteName('#__usergroups'))
        ->where($db->quoteName('title') . ' IN (' . $db->quote('Teacher') . ',' . $db->quote('Student') . ')')
)->loadColumn();

echo 'User groups: ' . implode(', ', $groups) . "\n";
echo "Done.\n";
