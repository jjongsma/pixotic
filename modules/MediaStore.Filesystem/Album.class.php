<?php
require_once(PIXOTIC.'/lib/pixotic/Album.class.php');
require_once(PIXOTIC.'/lib/pixotic/Sorter.class.php');
require_once('MediaItem.class.php');

class pixotic_MediaStore_Filesystem_Album implements pixotic_Album {

	private $path = null;
	private $parent = null;
	private $pixotic = null;

	private $name;
	private $items;
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
					$this->albums[] = new pixotic_MediaStore_Filesystem_Album($this->path.'/'.$d,
						$this, null, null, $this->pixotic);
			}
			closedir($dh);
		}
		// Sort by sub-album sorting preference, or $sort
		$this->albums = pixotic_Sorter::Sort($this->albums, $this->albumSort,
			$this->pixotic->getConfig('cacheDirectory'));
		return $this->albums;
	}

	public function getItems() {
		if ($this->items === null) {
			$this->items = array();
			$dh = opendir($this->path);
			while ($f = readdir($dh)) {
				if ($f{0} == '.')
					continue;
				if (is_file($this->path.'/'.$f)) {
					$ext = strtolower(array_pop(explode('.', $f)));
					if (in_array($ext, $this->validExts)) {
						$this->items[] = new pixotic_MediaStore_Filesystem_MediaItem($this->path.'/'.$f,
							$this, $this->pixotic);
					}
				}
			}
			closedir($dh);
		}
		// Sort by sub-album sorting preference, or $sort
		$this->items = pixotic_Sorter::Sort($this->items, $this->imageSort,
			$this->pixotic->getConfig('cacheDirectory'));
		return $this->items;
	}

	public function getName() {
		return $this->name;
	}

	public function getParent() {
		return $this->parent;
	}

	public function getID() {
		return substr($this->path, strlen($this->pixotic->getConfig('albumDirectory')) + 1);
	}

	public function getPath() {
		return $this->path;
	}

}

