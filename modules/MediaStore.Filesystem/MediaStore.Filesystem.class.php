<?php
require_once(PIXOTIC.'/lib/pixotic/Module.class.php');
require_once(PIXOTIC.'/lib/pixotic/MediaStore.class.php');
require_once('Album.class.php');
require_once('MediaItem.class.php');

class pixotic_MediaStore_Filesystem implements pixotic_Module, pixotic_MediaStore {

	private $pixotic;
	private $rootAlbum = null;

	public function activate(&$pixotic) {
		$this->pixotic =& $pixotic;
		$pixotic->registerService(pixotic_Service::$MEDIA_STORE, $this);
	}

	public function deactivate() {
	}

	private function getRootAlbum() {
		if (!$this->rootAlbum) {
			$this->rootAlbum = new pixotic_MediaStore_Filesystem_Album(
				$this->pixotic->getConfig('mediastore.filesystem.directory'),
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

	public function getItem($id) {
		return $this->findItem($id, $this->getAlbums());
	}

	private function findItem($id, $albums) {

		foreach ($albums as $album) {
			$path = $album->getRelPath();
			if ($path == substr($id, 0, strlen($path))) {
				foreach ($album->getItems() as $image) {
					if ($image->getRelPath() == $id)
						return $image;
				}
				return $this->findItem($id, $album->getAlbums());
			}
		}

		return null;

	}

	public function getDefaultAlbum($album = null) {

		if (!$album)
			$album = $this->getRootAlbum();

		$albums = $album->getAlbums();

		foreach ($albums as $a) {
			if (count($a->getItems()) > 0)
				return $a;
			if (count($a->getAlbums()) > 0) {
				$d = $this->getDefaultAlbum($a);
				if ($d)
					return $d;
			}
		}

		return null;

	}

	public function createAlbum($name, $parent = null) {
		throw new Exception('Unsupported action');
	}

	public function renameAlbum($albumId, $name) {
		throw new Exception('Unsupported action');
	}

	public function moveAlbum($albumId, $parent = null) {
		throw new Exception('Unsupported action');
	}

	public function deleteAlbum($albumId) {
		throw new Exception('Unsupported action');
	}

	public function createItem($name, $file, $album) {
		throw new Exception('Unsupported action');
	}

	public function renameItem($itemId, $name) {
		throw new Exception('Unsupported action');
	}

	public function moveItem($itemId, $album) {
		throw new Exception('Unsupported action');
	}

	public function deleteItem($itemId) {
		throw new Exception('Unsupported action');
	}

	public function isWritable() {
		return false;
	}

}
