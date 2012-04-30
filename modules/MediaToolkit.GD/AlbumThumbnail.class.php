<?php
require_once('ResizedImage.class.php');

class pixotic_AlbumIcon extends pixotic_ResizedImage {

	private $album;

	public function __construct($icon, $album, $size, $cache) {
		parent::__construct($icon, $size, $cache);
		$this->album = $album;
	}

	protected function getCacheFilename() {
		$ext = array_pop(explode('.', $this->path));
		return $this->cache.'/'.md5($this->album->getPath().'_'.$this->size).'.'.$ext;
	}

	public function getResizedImage() {

		$cacheFile = $this->getCacheFilename();

		if (file_exists($cacheFile) && filemtime($cacheFile) > filemtime($this->album->getPath()))
			return $cacheFile;

		// Icon needs recreating
		$resized = imagecreatefromfile(parent::getResizedImage());

		// Blend album images
		$images = $this->album->getImages();
		if (count($images) > 0) {
			$x = floor($this->size * 0.45);
			$y = floor($this->size * 0.53);
			$size = floor($this->size * 0.43);
			$this->overlayImage($resized, $images[0]->getPath(), $x, $y, $size, 0x999999);
		}
		if (count($images) > 1) {
			$x = floor($this->size * 0.45);
			$y = floor($this->size * 0.03);
			$size = floor($this->size * 0.43);
			$this->overlayImage($resized, $images[1]->getPath(), $x, $y, $size, 0x999999);
		}

		imagealphablending($resized, false);
		imagesavealpha($resized, true);

		list($width, $height, $type) = getimagesize($cacheFile);
		imagewrite($resized, $type, $cacheFile);

		return $cacheFile;

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

}
