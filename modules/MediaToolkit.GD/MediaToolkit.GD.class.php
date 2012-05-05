<?php
require_once(PIXOTIC.'/lib/pixotic/Module.class.php');
require_once(PIXOTIC.'/lib/pixotic/MediaToolkit.class.php');
require_once('imagefuncs.inc.php');

class pixotic_MediaToolkit_GD extends pixotic_MediaToolkit implements pixotic_Module {

	private $pixotic;
	private $cacheDir;

	public function activate(&$pixotic) {
		$this->pixotic = $pixotic;
		$this->cacheDir = $pixotic->getConfig('gallery.imageCache');
		$pixotic->registerService(pixotic_Service::$MEDIA_TOOLKIT, $this);
	}

	public function deactivate() {
		$pixotic->unregisterService(pixotic_Service::$MEDIA_TOOLKIT, $this);
	}

	public function getSupportedMimeTypes() {
		return array(
			'image/jpeg',
			'image/png',
			'image/gif',
			'image/bmp',
			'image/wbmp'
		);
	}

	public function getSupportedActions() {
		return array(
			pixotic_MediaToolkit::$ACTION_RESIZE,
			pixotic_MediaToolkit::$ACTION_ROTATE,
			pixotic_MediaToolkit::$ACTION_ALBUM_THUMBNAIL
		);
	}

	public function scaleItem($item, $size, $outFile) {

		$path = $item->getAbsolutePath();

		if (!$this->needsRefresh($item->getLastModified(), $outFile))
			return;

		list($width, $height) = getimagesize($path);
		$ratio = $this->getScaleRatio($width, $height, $size);
		$newwidth = intval($width * $ratio);
		$newheight = intval($height * $ratio);

		return $this->resizeItem($item, $newwidth, $newheight, $outFile);

	}

	public function resizeItem($item, $width, $height, $outFile, $cropX = 0, $cropY = 0, $cropWidth = 0, $cropHeight = 0) {

		$path = $item->getAbsolutePath();

		if (!$this->needsRefresh($item->getLastModified(), $outFile))
			return;

		$resized = $this->getResizedImage($path, $width, $height, $cropX, $cropY, $cropWidth, $cropHeight);
		$tmpFile = $outFile.'.tmp';
		list($w, $h, $type) = getimagesize($path);
		imagewrite($resized, $type, $tmpFile);
		rename($tmpFile, $outFile);

	}

	public function getMetadata($item) {
		throw new Exception('Unsupported action');
	}

	public function writeMetadata($item, $metadata) {
		throw new Exception('Unsupported action');
	}

	public function createAlbumThumbnail($album, $size, $format, $outFile) {

		if (!$this->needsRefresh($album->getLastModified(), $outFile))
			return;

		// Icon needs recreating
		$icon = $this->pixotic->getThemePath('images/folder_icon.png');
		list($width, $height) = getimagesize($icon);
		$ratio = $this->getScaleRatio($width, $height, $size);
		$newwidth = intval($width * $ratio);
		$newheight = intval($height * $ratio);
		$resized = $this->getResizedImage($icon, $newwidth, $newheight);
		imagealphablending($resized, false);
		imagesavealpha($resized, true);

		// Blend album images
		$items = $album->getItems();
		if (count($items) > 0) {
			$x = floor($size * 0.45);
			$y = floor($size * 0.53);
			$s = floor($size * 0.43);
			$this->overlayImage($resized, $items[0]->getAbsolutePath(), $x, $y, $s, 0x999999);
		}
		if (count($items) > 1) {
			$x = floor($size * 0.45);
			$y = floor($size * 0.03);
			$s = floor($size * 0.43);
			$this->overlayImage($resized, $items[1]->getAbsolutePath(), $x, $y, $s, 0x999999);
		}

		$types = array(
				'image/gif' => IMAGETYPE_GIF,
				'image/jpeg' => IMAGETYPE_JPEG,
				'image/png' => IMAGETYPE_PNG);

		$tmpFile = $outFile.'.tmp';
		imagewrite($resized, $types[$format], $tmpFile);
		rename($tmpFile, $outFile);

	}

	private function needsRefresh($timestamp, $cache) {
		return !file_exists($cache) || ($timestamp > filemtime($cache));
	}

	private function getResizedImage($path, $width, $height, $cropX = 0, $cropY = 0, $cropWidth = 0, $cropHeight = 0) {

		// Get image size
		list($owidth, $oheight, $type) = getimagesize($path);
		if (!$cropWidth) $cropWidth = $owidth;
		if (!$cropHeight) $cropHeight = $oheight;

		// Load
		$source = imagecreatefromfile($path, $type);
		$resized = imagecreatetruecolor($width, $height);
		imagealphablending($resized, false);
		imagesavealpha($resized, true);

		// Resize
		imagecopyresampled($resized, $source, 0, 0, $cropX, $cropY, $width, $height, $cropWidth, $cropHeight);

		// Check EXIF data for orientation
		if ($type == IMAGETYPE_JPEG) {
			$exif = exif_read_data($path);
			if ($exif)
				$resized = $this->fixOrientation($resized, $exif['Orientation']);
		}

		return $resized;

	}

	private function overlayImage(&$icon, $image, $x, $y, $size, $border = false) {

		list($width, $height, $type) = getimagesize($image);
		$overlay = imagecreatefromfile($image, $type);
		if ($overlay) {
			$srcx = 0;
			$srcy = 0;
			$srcsize = 0;
			if ($width > $height) {
				$srcsize = $height;
				$srcx = floor(($width - $height) / 2);
			} else {
				$srcsize = $width;
				$srcy = floor(($height - $width) / 2);
			}
			imagecopyresampled($icon, $overlay, $x, $y, $srcx, $srcy, $size, $size, $srcsize, $srcsize);
			if ($border) {
				imagerectangle($icon, $x - 1, $y - 1, $x + $size, $y + $size, 0xFFFFFF);
				imagerectangle($icon, $x - 2, $y - 2, $x + $size + 1, $y + $size + 1, 0xFFFFFF);
			}
		}

	}

	private function getScaleRatio($width, $height, $size) {

		$ratio = 1.0;

		// Calculate resize ratio
		if ($width > $height) {
			$ratio = $size / $width;
		} else {
			$ratio = $size / $height;
		}

		return $ratio;

	}

	private function fixOrientation(&$image, $orientation) {

		switch ($orientation) {

			case 2:
				return imageflip($image);

			case 3:
				return imagerotate($image, 180, 0);

			case 4:
				$i = imagerotate($image, 180, 0);
				return imageflip($i);

			case 5:
				$i = imagerotate($image, 270, 0);
				return imageflip($i);

			case 6:
				return imagerotate($image, 270, 0);

			case 7:
				$i = imagerotate($image, 90, 0);
				return imageflip($i);

			case 8:
				return imagerotate($image, 90, 0);

		}

		return $image;

	}
}
