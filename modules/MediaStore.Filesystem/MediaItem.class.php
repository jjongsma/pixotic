<?php
require_once(PIXOTIC.'/lib/pixotic/MediaItem.class.php');

class pixotic_MediaStore_Filesystem_MediaItem implements pixotic_MediaItem {

	private $path;
	private $album;
	private $pixotic;

	private $name;
	private $imageData;
	private $metadata;

	public function __construct($path, $album, $pixotic) {

		$this->path = $path;
		$this->album = $album;
		$this->pixotic = $pixotic;

		$this->name = basename($path);

	}

	public function getID() {
		return substr($this->path, strlen(realpath($this->pixotic->getConfig('mediastore.filesystem.directory'))) + 1);
	}

	public function getName() {
		return $this->name;
	}

	public function getAbsolutePath() {
		return $this->path;
	}

	public function getAlbum() {
		return $this->album;
	}

	public function getMimeType() {
		$id = $this->getImageData();
		return $id['mime'];
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
		$metadata = $this->pixotic->getMetadata($this);
		if ($metadata)
			return $metadata[pixotic_MediaToolkit::$METADATA_DESCRIPTION];
		return null;
	}

	public function getLastModified() {
		return filemtime($this->path);
	}

}

