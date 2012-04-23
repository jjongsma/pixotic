<?php
	require_once('imagefuncs.inc.php');

	// Sorting constants
	define('CUSTOM', 'CUSTOM');
	define('FILENAME', 'FILENAME');
	define('FILENAME_DESCENDING', 'FILENAME_DESCENDING');
	define('FILE_DATE', 'FILE_DATE');
	define('FILE_DATE_DESCENDING', 'FILE_DATE_DESCENDING');
	define('EXIF_DATE', 'EXIF_DATE');
	define('EXIF_DATE_DESCENDING', 'EXIF_DATE_DESCENDING');

	class Pixotic {
		
		private $config;
		private $allowLogin = true;
		private $baseUrl;

		private $templateEngine;
		private $albumManager;

		public function __construct() {

			include('config.inc.php');

			$this->config = get_defined_vars();
			$this->allowLogin = session_start() && $this->config['adminUsername'];
			$this->baseUrl = $this->getConfig('baseUrl', '');

			$this->templateEngine = new Pixotic_Templates(
				$this->getConfig('theme', 'default'), $this);
			$this->albumManager = new Pixotic_AlbumManager($this);

		}

		// Configuration management

		public function getConfig($name, $default = null) {
			if (isset($this->config[$name]))
				return $this->config[$name];
			return $default;
		}

		public function getRealPath($path = null) {
			return dirname(dirname(__FILE__)).'/'.$path;
		}

		public function getRealURL($path = null) {
			return $this->baseUrl.$path;
		}

		// Login management

		public function login($username, $password) {
			if ($this->allowLogin
					&& $this->config['adminUsername'] == $username
					&& $this->config['adminPassword'] == $password) {
				$_SESSION['loggedIn'] = true;
				return true;
			}
			$_SESSION['loggedIn'] = false;
			return false;
		}

		public function logout() {
			$_SESSION['loggedIn'] = false;
		}

		public function isLoggedIn() {
			if ($this->allowLogin && isset($_SESSION['loggedIn']))
				return $_SESSION['loggedIn'];
			return false;
		}

		public function isAdmin() {
			// No separate permissions for now
			return $this->isLoggedIn();
		}

		// Page / template display

		public function showPage($template, $context = null) {
			$this->templateEngine->showPage($template, $context);
		}

		// Root albums

		public function getAlbums() {
			return $this->albumManager->getAlbums();
		}

		public function getDefaultAlbum() {
			return $this->albumManager->getDefaultAlbum();
		}

		public function getAlbum($id) {
			return $this->albumManager->getAlbum($id);
		}

		public function getImage($id) {
			return $this->albumManager->getImage($id);
		}

	}

	class Pixotic_Templates {

		private $pixotic;
		private $theme = 'default';
		private $deviceType = 'desktop';

		private $themeRoot;
		private $defaultContext;

		public function __construct($theme, $pixotic) {

			$this->theme = $theme;
			$this->pixotic = $pixotic;
			$this->deviceType = $this->detectDeviceType();

			$this->themeRoot = dirname(dirname(__FILE__)).'/themes';
			$this->defaultContext = array(
				'pixotic' => $this->pixotic
			);

		}

		private function detectDeviceType() {
			return 'desktop';
		}

		private function getThemeRelativeURL($path = null) {

			$themeUrl = '/themes/'.$this->theme.'/'.$path;
			$deviceUrl = '/themes/'.$this->theme.'/'.$this->deviceType.'/'.$path;

			return file_exists($this->pixotic->getRealPath($deviceUrl))
					? $deviceUrl
					: $themeUrl;

		}

		private function getThemeURL($path = null) {
			return $this->pixotic->getRealURL($this->getThemeRelativeURL($path));
		}

		public function showPage($template, $context = null) {
			$context = $this->makeContext($context);
			$content = $this->fetchTemplate($template, $context);
			$context['content'] = $content;
			echo $this->fetchTemplate('base.tpl', $context);
		}

		public function showBlock($template, $context = null) {
			echo $this->fetchTemplate($template,
				$this->makeContext($context));
		}

		private function fetchTemplate($template, $context = null) {

			if ($context)
				extract($context);

			$realTemplate = $this->pixotic->getRealPath($this->getThemeRelativeURL($template));

			if (!file_exists($realTemplate))
				return $this->fetchTemplate('notfound.tpl', array(
					'title' => 'Template Not Found',
					'error' => 'The template <b>'.$template.'</b> was not found.'));

			ob_start();
			include($realTemplate);
			$block = ob_get_contents();
			ob_end_clean();

			return $block;

		}
		
		private function makeContext($context = null) {

			if (!$context)
				$context = array();

			return array_merge($this->defaultContext, $context);

		}

		public function getAlbumNavigation($active = null, $parent = null) {

			$albums = $parent ? $parent->getAlbums() : $this->pixotic->getAlbums();
			$albumNav = array();

			foreach ($albums as $a) {

				$albumEntry = array(
					'name' => $a->getName(),
					'path' => $a->getRelPath());

				if (substr($active, 0, strlen($a->getRelPath())) == $a->getRelPath()) {
					if ($a->getRelPath() == $active)
						$albumEntry['selected'] = true;
					if (count($a->getAlbums()) > 0)
						$albumEntry['albums'] = $this->getAlbumNavigation($active, $a);
				}

				$albumNav[] = $albumEntry;

			}

			return $albumNav;

		}

	}

	class Pixotic_AlbumManager {

		private $pixotic;
		private $rootAlbum = null;

		public function __construct($pixotic) {
			$this->pixotic = $pixotic;
		}

		private function getRootAlbum() {
			if (!$this->rootAlbum) {
				$this->rootAlbum = new Pixotic_Album($this->pixotic->getConfig('albumDirectory'),
					null,
					$this->pixotic->getConfig('albumSort', FILENAME_ASCENDING),
					$this->pixotic->getConfig('imageSort', FILENAME_ASCENDING),
					$this->pixotic);
			}
			return $this->rootAlbum;
		}

		public function getAlbums() {
			return $this->getRootAlbum()->getAlbums();
		}

		public function getAlbum($id) {
			return $this->findAlbum($id, $this->getAlbums());
		}

		private function findAlbum($id, $albums) {

			foreach ($albums as $album) {
				$path = $album->getRelPath();
				if ($path == $id) {
					return $album;
				} elseif ($path == substr($id, 0, strlen($path))) {
					return $this->findAlbum($id, $album->getAlbums());
				}
			}

			return null;

		}

		public function getImage($id) {
			return $this->findImage($id, $this->getAlbums());
		}

		private function findImage($id, $albums) {

			foreach ($albums as $album) {
				$path = $album->getRelPath();
				if ($path == substr($id, 0, strlen($path))) {
					foreach ($album->getImages() as $image) {
						if ($image->getRelPath() == $id)
							return $image;
					}
					return $this->findImage($id, $album->getAlbums());
				}
			}

			return null;

		}

		public function getDefaultAlbum($album = null) {

			if (!$album)
				$album = $this->getRootAlbum();

			$albums = $album->getAlbums();

			foreach ($albums as $a) {
				if (count($a->getImages()) > 0)
					return $a;
				if (count($a->getAlbums()) > 0) {
					$d = $this->getDefaultAlbum($a);
					if ($d)
						return $d;
				}
			}

			return null;

		}

	}

	class Pixotic_Album {

		private $path = null;
		private $parent = null;
		private $pixotic = null;

		private $name;
		private $images;
		private $albums;
		private $sort;

		private $validExts = array(
			'jpg', 'jpeg', 'gif', 'bmp', 'png', 'svg', 'tif', 'tiff'
		);

		public function __construct($path, $parent, $albumSort, $imageSort, $pixotic) {

			$this->path = $path;
			$this->parent = $parent;
			$this->albumSort = $albumSort ? $albumSort : $pixotic->getConfig('subAlbumSort', FILENAME);
			$this->imageSort = $imageSort ? $imageSort : $pixotic->getConfig('imageSort', FILENAME);
			$this->pixotic = $pixotic;

			$this->name = basename($path);

		}

		public function setPrivate($private = true) {

			$market = $this->path.'/.private';

			if ($private)
				touch($marker);

			else if (file_exists($marker))
				unlink($marker);

		}

		public function getAlbums($private = false) {
			if ($this->albums === null) {
				$this->albums = array();
				$dh = opendir($this->path);
				while ($d = readdir($dh)) {
					if ($d{0} == '.')
						continue;
					if (!$private && file_exists($this->path.'/'.$d.'/.private'))
						continue;
					if (is_dir($this->path.'/'.$d))
						$this->albums[] = new Pixotic_Album($this->path.'/'.$d,
							$this, null, null, $this->pixotic);
				}
				closedir($dh);
			}
			// Sort by sub-album sorting preference, or $sort
			$this->albums = Pixotic_Sorter::Sort($this->albums, $this->albumSort,
				$this->pixotic->getConfig('cacheDirectory'));
			return $this->albums;
		}

		public function getImages() {
			if ($this->images === null) {
				$this->images = array();
				$dh = opendir($this->path);
				while ($f = readdir($dh)) {
					if ($f{0} == '.')
						continue;
					if (is_file($this->path.'/'.$f)) {
						$ext = strtolower(array_pop(explode('.', $f)));
						if (in_array($ext, $this->validExts)) {
							$this->images[] = new Pixotic_Image($this->path.'/'.$f,
								$this, $this->pixotic);
						}
					}
				}
				closedir($dh);
			}
			// Sort by sub-album sorting preference, or $sort
			$this->images = Pixotic_Sorter::Sort($this->images, $this->imageSort,
				$this->pixotic->getConfig('cacheDirectory'));
			return $this->images;
		}

		public function getName() {
			return $this->name;
		}

		public function getParent() {
			return $this->parent;
		}

		public function getPath() {
			return $this->path;
		}

		public function getRelPath() {
			return substr($this->path, strlen($this->pixotic->getConfig('albumDirectory')) + 1);
		}

	}

	class Pixotic_Image {

		private $path;
		private $album;
		private $pixotic;

		private $name;
		private $imageData;
		private $exifData;
		private $thumbnail;
		private $resized;
		private $fullsize;

		public function __construct($path, $album, $pixotic) {

			$this->path = $path;
			$this->album = $album;
			$this->pixotic = $pixotic;

			$this->name = basename($path);

		}

		public function getName() {
			return $this->name;
		}

		public function getAlbum() {
			return $this->album;
		}

		public function getPath() {
			return $this->path;
		}

		public function getRelPath() {
			return substr($this->path, strlen($this->pixotic->getConfig('albumDirectory')) + 1);
		}

		private function getImageData() {
			if (!$this->imageData) {
				$this->imageData = getimagesize($this->path);
			}
			return $this->imageData;
		}

		public function getWidth() {
			$d = $this->getImageData();
			return $d[0];
		}

		public function getHeight() {
			$d = $this->getImageData();
			return $d[1];
		}

		public function getDescription() {
			$exif = $this->getExifData();
			return $exif['Description'];
		}

		public function getThumbnail() {
			if (!$this->thumbnail) {
				$this->thumbnail = new Pixotic_ResizedImage(
					$this->path, $this->album, $this->pixotic,
					$pixotic->getConfig('thumbnailSize', 128),
					$this->pixotic->getConfig('cacheDirectory'));
			}
			return $this->thumbnail;
		}

		public function getResized() {
			if (!$this->resized) {
				$this->resized = new Pixotic_ResizedImage($this->path,
					$pixotic->getConfig('imageSize', 640),
					$this->pixotic->getConfig('cacheDirectory'));
			}
			return $this->thumbnail;
		}

		public function getFullSize() {
			if (!$this->fullsize)
				$this->fullsize = new Pixotic_ResizedImage($this->path, 0,
					$this->pixotic->getConfig('cacheDirectory'));
			return $this->fullsize;
		}

		private function computeExifValue($val) {

			if ($val) {
				$tmp = explode('/', $val);
				if (count($tmp) == 2)
					return $tmp[0] / $tmp[1];
			}

			return $val;

		}

		public function getExifData() {

			if (!$this->exifData) {

				$exif = exif_read_data($this->path);

				if ($exif) {

					$aperture = 'f/'.$this->computeExifValue($exif['FNumber']);
					$focal = $this->computeExifValue($exif['FocalLength']).'mm';
					$shutter = $exif['ExposureTime'];
					if ($shutter) {
						$tmp = explode('/', $shutter);
						if (count($tmp) == 2) {
							$div = $tmp[0];
							$shutter = '1/'.($tmp[1]/$div).' sec';
						}
					}

					$metermodes = array(
						'Unknown',
						'Average',
						'Center Weighted',
						'Spot',
						'Multi-Spot',
						'Matrix',
						'Partial');
					$metermode = $exif['MeteringMode'];
					if (isset($metermodes[$metermode]))
						$metermode = $metermodes[$metermode];

					$expbias = $exif['ExposureBiasValue'];

					$expmodes = array(
						'Unknown',
						'Manual',
						'Normal',
						'Aperture Priority',
						'Shutter Priority',
						'Creative',
						'Action',
						'Portrait',
						'Landscape');
					$expmode = $exif['ExposureProgram'];
					if (isset($expmodes[$expmode]))
						$expmode = $expmodes[$expmode];

					$data = array(
						'Camera Make' => $exif['Make'],
						'Camera Model' => $exif['Model'],
						'Aperture' => $aperture,
						'Shutter Speed' => $shutter,
						'ISO' => $exif['ISOSpeedRatings'],
						'Focal Length' => $focal,
						'Flash' => $exif['Flash'] ? 'Flash' : 'No Flash',
						'Color Space' => $exif['ColorSpace'] == 1 ? 'sRGB' : 'Unknown',
						'Exposure Bias' => $expbias,
						'Exposure Program' => $expmode,
						'Metering Mode' => $metermode,
						'Date Taken' => $exif['DateTimeOriginal'],
						'Description' => $exif['ImageDescription']
							? trim($exif['ImageDescription'])
							: trim($exif['COMPUTED']['UserComment'])
					);

					$this->exifData = $data;

				} else {

					$this->exifData = null;
				
				}

			}

			return $this->exifData;

		}

	}

	class Pixotic_ResizedImage {

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

	class Pixotic_AlbumIcon extends Pixotic_ResizedImage {

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

	// Static sort helper
	class Pixotic_Sorter {

		private static $handlers = array(
			CUSTOM => 'SortByCustom',
			FILENAME => 'SortByFilename',
			FILENAME_DESCENDING => 'SortByFilenameDesc',
			FILE_DATE => 'SortByFileDate',
			FILE_DATE_DESCENDING => 'SortByFileDateDesc',
			EXIF_DATE => 'SortByExifDate',
			EXIF_DATE_DESCENDING => 'SortByExifDateDesc'
		);

		private static $cacheable = array(
			CUSTOM => 'custom',
			EXIF_DATE => 'exif',
			EXIF_DATE_DESCENDING => 'exifdesc'
		);

		private static $exifCache = array();

		// Usage contract: all items to be sorted must be of the same type and
		// in the same directory
		public static function Sort(&$ary, $type = FILENAME, $cacheDir = null) {

			if (!count($ary))
				return $ary;

			$refitem = reset($ary);
			$dir = dirname($refitem->getPath());

			// EXIF not supported for directories
			if ($refitem instanceof Pixotic_Album) {
				if ($type == EXIF_DATE)
					$type = FILE_DATE;
				elseif ($type == EXIF_DATE_DESCENDING)
					$type = FILE_DATE_DESCENDING;
			}

			$sortCache = null;
			$refreshCache = false;
			Pixotic_Sorter::$exifCache = array();

			if (isset(Pixotic_Sorter::$cacheable[$type])) {

				$cacheKey = md5($dir.'-'.$type);
				$sortCache = $cacheDir.'/'.Pixotic_Sorter::$cacheable[$type].'sort-'.$cacheKey;

				if (file_exists($sortCache)) {

					$cacheTime = filemtime($sortCache);

					if (filemtime($dir) > $cacheTime) {
						$refreshCache = true;
					} else {
						$dh = opendir($dir);
						while ($f = readdir($dh)) {
							if (filemtime($dir.'/'.$f) > $cacheTime) {
								$refreshCache = true;
								break;
							}
						}
						closedir($dh);
					}

				} else {
					$refreshCache = true;
				}

				if ($refreshCache) {

					usort($ary, array('Pixotic_Sorter', Pixotic_Sorter::$handlers[$type]));
					Pixotic_Sorter::CacheSortOrder($sortCache, $ary);

				} else {
				
					// Sort like cache file
					$sorted = array();
					$order = array_flip(explode("\n", trim(file_get_contents($sortCache))));

					foreach ($ary as $f) {
						$sorted[$order[$f->getPath()]] = $f;
						unset($order[$f->getPath()]);
					}

					ksort($sorted);

					// Add any missing items
					if (count($order) > 0) {
						$sorted = array_merge($sorted, array_flip($order));
						Pixotic_Sorter::CacheSortOrder($sortCache, $sorted);
					}

					return $sorted;

				}

			} else {

				usort($ary, array('Pixotic_Sorter', Pixotic_Sorter::$handlers[$type]));

			}

			return $ary;

		}

		private static function CacheSortOrder($cacheFile, &$items) {

			if (is_dir(dirname($cacheFile))) {

				$fh = fopen($cacheFile.'.tmp', 'w');

				if (flock($fh, LOCK_EX)) {
					foreach ($items as $f)
						fwrite($fh, $f->getPath()."\n");
					fflush($fh);
					flock($fh, LOCK_UN);
				}

				fclose($fh);

				rename($cacheFile.'.tmp', $cacheFile);

			}

		}

		public static function SortByFilename($a, $b) {
			$f1 = basename($a->getPath());
			$f2 = basename($b->getPath());
			return strcmp($f1, $f2);
		}

		public static function SortByFileDate($a, $b) {

			$f1 = $a->getPath();
			$f2 = $b->getPath();

			if (!file_exists($f1)) {
				if (file_exists($f2))
					return -1;
				return 0;
			}


			if (!file_exists($f2))
				return 1;
			$m1 = filemtime($f1);
			$m2 = filemtime($f2);

			if ($m1 < $m2)
				return -1;
			else if ($m1 > $m2)
				return 1;

			return 0;

		}

		public static function SortByExifDate($a, $b) {

			$e1 = Pixotic_Sorter::GetExifDate($a->getPath());
			$e2 = Pixotic_Sorter::GetExifDate($b->getPath());

			if ($e1 < $e2)
				return -1;
			else if ($e1 > $e2)
				return 1;

			return 0;

		}

		private static function GetExifDate($file) {

			if (!file_exists($file))
				return 0;

			if (is_dir($file))
				return filemtime($file);

			if (isset(Pixotic_Sorter::$exifCache[$file]))
				return Pixotic_Sorter::$exifCache[$file];

			$exif = exif_read_data($file);

			if (!$exif)
				return filemtime($file);

			$exifDate = null;
			foreach (array('DateTimeOriginal', 'DateTimeDigitized', 'DateTime', 'FileDateTime') as $tag) {
				if (isset($exif[$tag])) {
					$exifDate = strtotime($exif[$tag]);
					break;
				}
			}

			if (!$exifDate)
				return filemtime($file);

			Pixotic_Sorter::$exifCache[$file] = $exifDate;

			return $exifDate;

		}

		// Reverse versions

		public static function SortByFilenameDesc($a, $b) {
			return Pixotic_Sorter::SortByFilename($b, $a);
		}

		public static function SortByFileDateDesc($a, $b) {
			return Pixotic_Sorter::SortByFileDate($b, $a);
		}

		public static function SortByExifDateDesc($a, $b) {
			return Pixotic_Sorter::SortByExifDate($b, $a);
		}

	}
