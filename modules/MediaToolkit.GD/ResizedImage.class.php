<?php
require_once('imagefuncs.inc.php');

class pixotic_ResizedImage {

	protected $path = null;
	protected $size = null;
	protected $cache = null;

	public function __construct($path, $size, $cache) {
		$this->path = $path;
		$this->size = $size;
		$this->cache = $cache;
	}

	protected function getCacheFilename() {
		$ext = array_pop(explode('.', $this->path));
		return $this->cache.'/'.md5($this->path.'_'.$this->size).'.'.$ext;
	}

	
	protected function getSizeRatio($target, $width = 0, $height = 0) {

		$ratio = 1.0;

		// Get image size
		if (!$width)
			list($width, $height) = getimagesize($this->path);

		// Calculate resize ratio
		if ($width > $height) {
			$ratio = $target / $width;
		} else {
			$ratio = $target / $height;
		}

		$newwidth = $width * $ratio;
		$newheight = $height * $ratio;

		return $ratio;

	}

	public function getResizedImage() {

		if (!$this->size)
			return $this->path;

		$cacheFile = $this->getCacheFilename();

		if (file_exists($cacheFile) && filemtime($cacheFile) > filemtime($this->path))
			return $cacheFile;

		// Get new image size
		list($width, $height, $type) = getimagesize($this->path);
		$ratio = $this->getSizeRatio($this->size, $width, $height);
		$newwidth = floor($width * $ratio);
		$newheight = floor($height * $ratio);

		// Load
		$source = imagecreatefromfile($this->path, $type);
		$resized = imagecreatetruecolor($newwidth, $newheight);
		imagealphablending($resized, false);
		imagesavealpha($resized, true);

		// Resize
		imagecopyresampled($resized, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

		// Check EXIF data for orientation
		if ($type == IMAGETYPE_JPEG) {
			$exif = exif_read_data($this->path);
			if ($exif)
				$resized = $this->fixOrientation($resized, $exif['Orientation']);
		}

		imagewrite($resized, $type, $cacheFile);

		return $cacheFile;

	}

	// Send file with correct cache headers

	public function send($download = false) {

		$overrides = array(
			'.css' => 'text/css',
			'.html' => 'text/html',
			'.js' => 'text/javascript',
			'.css' => 'text/css',
			'.png' => 'image/png',
			'.jpg' => 'image/jpeg',
			'.jpeg' => 'image/jpeg',
			'.gif' => 'image/gif'
		);

		$file = $this->getResizedImage();

		if (file_exists($file) && is_file($file)) {
		
			if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER)) {
				$lastmod = filemtime($file);
				$lastreq = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
				if (array_key_exists('HTTP_CACHE_CONTROL', $_SERVER)) {
					$cc = $_SERVER['HTTP_CACHE_CONTROL'];
					// max-age just specifies to validate against modified time
					//if ($cc == 'max-age=0' || $cc = 'no-cache')
					if ($cc == 'no-cache')
						$lastreq = 0;
				}
				if ($lastmod <= $lastreq) {
					header('HTTP/1.1 304 Not Modified');
					header('Pragma: phpsucks');
					header('Cache-Control: public');
					exit;
				}
			}

			$ext = substr($file, strrpos($file, '.'));

			if ($ext == '.php') {

				include($file);

			} else {

				if (array_key_exists($ext, $overrides))
					$mimetype = $overrides[$ext];
				else
					//$mimetype = mime_content_type($file);
					$mimetype = 'text/plain';
				header('Pragma: phpsucks');
				header('Cache-Control: public');
				header('Content-type: '.$mimetype);
				header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
				if ($download) {
					header('Content-Disposition: attachment; filename='.basename($file));
				}

				if ($fh = fopen($file, 'r')) {
					while (!feof($fh))
						echo fread($fh, 8192);
					fclose($fh);
				}

			}

		} else {

			header('Status: 404 Not Found');
			echo 'Unable to cache image.  Please check your configuration.';

		}

	}

	protected function fixOrientation(&$image, $orientation) {

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
