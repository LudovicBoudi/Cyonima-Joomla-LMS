<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

/**
 * Cyonima component helper.
 */
abstract class CyonimaHelper
{
	/**
	 * The user group title for teachers.
	 *
	 * @var string
	 */
	public const GROUP_TEACHER = 'Teacher';

	/**
	 * The user group title for students.
	 *
	 * @var string
	 */
	public const GROUP_STUDENT = 'Student';

	/**
	 * Returns the component parameters.
	 *
	 * @return  Registry
	 */
	public static function getParams(): Registry
	{
		return ComponentHelper::getParams('com_cyonima');
	}

	/**
	 * Returns the group id for a given group title.
	 *
	 * @param   string  $title  Group title.
	 *
	 * @return  integer
	 */
	public static function getGroupId(string $title): int
	{
		static $cache = [];

		if (isset($cache[$title])) {
			return $cache[$title];
		}

		$db = Factory::getContainer()->get('DatabaseDriver');

		$id = (int) $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__usergroups'))
				->where($db->quoteName('title') . ' = :title')
				->bind(':title', $title)
		)->loadResult();

		return $cache[$title] = $id;
	}

	/**
	 * Checks whether the given user belongs to a group.
	 *
	 * @param   string       $title  Group title.
	 * @param   User|null    $user   The user, defaults to current.
	 *
	 * @return  boolean
	 */
	public static function inGroup(string $title, ?User $user = null): bool
	{
		$user  = $user ?: Factory::getApplication()->getIdentity();
		$gid   = self::getGroupId($title);

		if (!$gid) {
			return false;
		}

		return \in_array($gid, $user->getAuthorisedGroups(), true);
	}

	/**
	 * Whether the user is a teacher.
	 *
	 * @param   User|null  $user  The user.
	 *
	 * @return  boolean
	 */
	public static function isTeacher(?User $user = null): bool
	{
		$user = $user ?: Factory::getApplication()->getIdentity();

		return $user->authorise('core.edit', 'com_cyonima') || self::inGroup(self::GROUP_TEACHER, $user);
	}

	/**
	 * Whether the user is a student.
	 *
	 * @param   User|null  $user  The user.
	 *
	 * @return  boolean
	 */
	public static function isStudent(?User $user = null): bool
	{
		$user = $user ?: Factory::getApplication()->getIdentity();

		return self::inGroup(self::GROUP_STUDENT, $user);
	}

	/**
	 * Renders a media picker field (Media Manager file browser) for a manual form.
	 *
	 * @param   string  $name   The field name (e.g. "jform[media]").
	 * @param   string  $value  The current value.
	 * @param   string  $id     The field id.
	 * @param   string  $types  Comma separated media types (images,audios,videos,documents).
	 *
	 * @return  string  The field HTML.
	 */
	public static function mediaField(string $name, string $value, string $id, string $types = 'images,audios,videos,documents'): string
	{
		$doc = Factory::getApplication()->getDocument();
		$wam = $doc->getWebAssetManager();

		$wam->useStyle('webcomponent.field-media')
			->useScript('webcomponent.field-media')
			->useScript('webcomponent.media-select');

		$mediaParams = ComponentHelper::getParams('com_media');

		$map = [
			'images'    => ['0', 'image_extensions', 'bmp,gif,jpg,jpeg,png,webp'],
			'audios'    => ['1', 'audio_extensions', 'mp3,m4a,mp4a,ogg'],
			'videos'    => ['2', 'video_extensions', 'mp4,mp4v,mpeg,mov,webm'],
			'documents' => ['3', 'doc_extensions', 'doc,odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv'],
		];

		$mediaTypeNames = array_map('trim', explode(',', $types));
		$typeIds        = [];
		$allowed        = ['images' => [], 'audios' => [], 'videos' => [], 'documents' => []];

		foreach ($mediaTypeNames as $typeName) {
			if ($typeName === 'directories') {
				$typeIds[] = '-1';

				continue;
			}

			if (!isset($map[$typeName])) {
				continue;
			}

			[$idValue, $paramKey, $default] = $map[$typeName];
			$typeIds[]                     = $idValue;
			$allowed[$typeName]            = array_map('trim', explode(',', $mediaParams->get($paramKey, $default)));
		}

		sort($typeIds);

		if (!$doc->getScriptOptions('media-picker')) {
			$doc->addScriptOptions('media-picker', [
				'images'    => array_map('trim', explode(',', $mediaParams->get('image_extensions', 'bmp,gif,jpg,jpeg,png,webp'))),
				'audios'    => array_map('trim', explode(',', $mediaParams->get('audio_extensions', 'mp3,m4a,mp4a,ogg'))),
				'videos'    => array_map('trim', explode(',', $mediaParams->get('video_extensions', 'mp4,mp4v,mpeg,mov,webm'))),
				'documents' => array_map('trim', explode(',', $mediaParams->get('doc_extensions', 'doc,odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv'))),
			]);
		}

		$doc->addScriptOptions('media-picker-api', ['apiBaseUrl' => Uri::base(true) . '/index.php?option=com_media&format=json']);

		$asset = Factory::getApplication()->getInput()->get('option');
		$url   = Route::_('index.php?option=com_media&view=media&tmpl=component&mediatypes=' . implode(',', $typeIds) . '&asset=' . $asset);

		$attr = static function (string $str): string {
			return htmlspecialchars($str, ENT_COMPAT, 'UTF-8');
		};

		return '<joomla-field-media'
			. ' types="' . $attr(implode(',', $mediaTypeNames)) . '"'
			. ' base-path="' . $attr(Uri::root()) . '"'
			. ' root-folder="' . $attr($mediaParams->get('image_path', 'images')) . '"'
			. ' url="' . $attr($url) . '"'
			. ' input=".field-media-input"'
			. ' button-select=".button-select"'
			. ' button-clear=".button-clear"'
			. ' modal-title="' . $attr(Text::_('JLIB_FORM_CHANGE_IMAGE')) . '"'
			. ' preview="false"'
			. ' preview-container=".field-media-preview"'
			. ' preview-width="0"'
			. ' preview-height="0"'
			. ' supported-extensions="' . $attr(json_encode($allowed)) . '"'
			. '>'
			. '<div class="input-group">'
			. '<input type="text" name="' . $attr($name) . '" id="' . $attr($id) . '" value="' . $attr($value) . '" class="form-control field-media-input">'
			. '<button type="button" class="btn btn-success button-select">' . Text::_('JLIB_FORM_BUTTON_SELECT') . '</button>'
			. '<button type="button" class="btn btn-danger button-clear"><span class="icon-times" aria-hidden="true"></span><span class="visually-hidden">' . Text::_('JLIB_FORM_BUTTON_CLEAR') . '</span></button>'
			. '</div>'
			. '</joomla-field-media>';
	}
}
