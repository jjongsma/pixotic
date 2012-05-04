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

	private $sorter;

	private $validExts = array(
		'jpg', 'jpeg', 'gif', 'bmp', 'png', 'svg', 'tif', 'tiff'
	);

	public function __construct($path, $parent, $albumSort, $imageSort, $pixotic) {

		$this->path = $path;
		$this->parent = $parent;
		$this->albumSort = $albumSort ? $albumSort : $pixotic->getConfig('gallery.sorting.albums', FILENAME);
		$this->imageSort = $imageSort ? $imageSort : $pixotic->getConfig('gallery.sorting.items', FILENAME);
		$this->pixotic = $pixotic;

		$this->sorter = new pixotic_Sorter($pixotic->getService(pixotic_Service::$CACHE));

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
		$this->albums = $this->sorter->sort($this->albums, $this->albumSort);
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
		$this->items = $this->sorter->sort($this->items, $this->imageSort);
		return $this->items;
	}

	public function getName() {
		return $this->name;
	}

	public function getAlbum() {
		return $this->parent;
	}

	public function getID() {
		return substr($this->path, strlen(realpath($this->pixotic->getConfig('mediastore.filesystem.directory'))) + 1);
	}

	public function getPath() {
		return $this->path;
	}

	public function getLastModified() {
		return filemtime($this->path);
	}

}

