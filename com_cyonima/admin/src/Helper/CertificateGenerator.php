<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Generates personalised certificates from a template image using GD.
 *
 * The template stores an image (JPEG/PNG) and a JSON "params" structure
 * describing where to draw each placeholder:
 *
 * {
 *   "name":   {"x": 0, "y": 400, "size": 48, "color": "#000000", "align": "center"},
 *   "course": {"x": 0, "y": 500, "size": 28, "color": "#333333", "align": "center"},
 *   "date":   {"x": 0, "y": 560, "size": 22, "color": "#666666", "align": "center"},
 *   "number": {"x": 0, "y": 120, "size": 18, "color": "#999999", "align": "right"}
 * }
 *
 * When "align" is set, the value is drawn relative to the image width.
 */
class CertificateGenerator
{
	/**
	 * Render the certificate and write it to the destination path.
	 *
	 * @param   string  $templatePath  Absolute path to the template image.
	 * @param   array   $placeholders  Associative array of placeholder values.
	 * @param   array   $positions     Position configuration (see class docblock).
	 * @param   string  $outputPath    Absolute path to write the PNG certificate.
	 * @param   string  $fontPath      Optional path to a TrueType font.
	 *
	 * @return  boolean
	 */
	public static function generate(string $templatePath, array $placeholders, array $positions, string $outputPath, string $fontPath = ''): bool
	{
		if (!\function_exists('imagecreatefromjpeg') && !\function_exists('imagecreatefrompng')) {
			throw new \RuntimeException(Text::_('COM_CYONIMA_ERROR_GD_NOT_AVAILABLE'));
		}

		$image = self::loadImage($templatePath);

		if (!$image) {
			throw new \RuntimeException(Text::_('COM_CYONIMA_ERROR_TEMPLATE_IMAGE'));
		}

		$width  = imagesx($image);
		$height = imagesy($image);

		foreach ($placeholders as $key => $value) {
			if ($value === '' || $value === null) {
				continue;
			}

			$config = $positions[$key] ?? [];
			self::drawText($image, (string) $value, $config, $width, $fontPath);
		}

		$dir = \dirname($outputPath);

		if (!is_dir($dir)) {
			\Joomla\Filesystem\Folder::create($dir);
		}

		$result = imagepng($image, $outputPath);
		imagedestroy($image);

		return (bool) $result;
	}

	/**
	 * Load an image resource from a JPEG or PNG file.
	 *
	 * @param   string  $path  Image path.
	 *
	 * @return  resource|\GdImage|false
	 */
	private static function loadImage(string $path)
	{
		if (!is_file($path)) {
			return false;
		}

		$info = @getimagesize($path);

		if (!$info) {
			return false;
		}

		switch ($info[2]) {
			case IMAGETYPE_JPEG:
				return \function_exists('imagecreatefromjpeg') ? imagecreatefromjpeg($path) : false;
			case IMAGETYPE_PNG:
				return \function_exists('imagecreatefrompng') ? imagecreatefrompng($path) : false;
			default:
				return false;
		}
	}

	/**
	 * Draw a single text placeholder.
	 *
	 * @param   resource|\GdImage  $image   Image resource.
	 * @param   string             $text    Text to draw.
	 * @param   array              $config  Position config.
	 * @param   integer            $width   Image width.
	 * @param   string             $fontPath  Font path.
	 *
	 * @return  void
	 */
	private static function drawText($image, string $text, array $config, int $width, string $fontPath): void
	{
		$x      = (int) ($config['x'] ?? 0);
		$y      = (int) ($config['y'] ?? 0);
		$size   = (int) ($config['size'] ?? 24);
		$align  = $config['align'] ?? 'left';
		$color  = self::allocateColor($image, $config['color'] ?? '#000000');
		$font   = $fontPath ?: ($config['font'] ?? '');

		if ($font && is_file($font)) {
			$box = imagettfbbox($size, 0, $font, $text);
			$tw  = abs($box[4] - $box[0]);

			switch ($align) {
				case 'center':
					$x = (int) (($width - $tw) / 2);
					break;
				case 'right':
					$x = (int) ($width - $tw - $x);
					break;
				default:
					break;
			}

			imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
		} else {
			$tw = \strlen($text) * (int) ($size * 0.6);

			switch ($align) {
				case 'center':
					$x = (int) (($width - $tw) / 2);
					break;
				case 'right':
					$x = (int) ($width - $tw - $x);
					break;
				default:
					break;
			}

			imagestring($image, 5, $x, $y, $text, $color);
		}
	}

	/**
	 * Allocate a colour from a hex string.
	 *
	 * @param   resource|\GdImage  $image  Image resource.
	 * @param   string             $hex    Hex colour.
	 *
	 * @return  integer
	 */
	private static function allocateColor($image, string $hex): int
	{
		$hex = ltrim($hex, '#');

		if (\strlen($hex) === 3) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$r = (int) hexdec(substr($hex, 0, 2));
		$g = (int) hexdec(substr($hex, 2, 2));
		$b = (int) hexdec(substr($hex, 4, 2));

		return imagecolorallocate($image, $r, $g, $b);
	}
}
