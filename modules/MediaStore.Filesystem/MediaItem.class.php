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
		return substr($this->path, strlen($this->pixotic->getConfig('albumDirectory')) + 1);
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

	private function getImageData() {
		if (!$this->imageData) {
			$this->imageData = getimagesize($this->path);
		}
		return $this->imageData;
	}

	private function getMetadata() {

		if (!$this->metadata) {

			$id = $this->getImageData();
			$toolkits = $pixotic->getMediaToolkit($id['mime']);
			$toolkit = null;

			foreach ($toolkits as $tk) {
				if (in_array(pixotic_MediaToolkit::$ACTION_READ_METADATA,
						$tk->getSupportedActions())) {
					$toolkit = $tk;
					break;
				}
			}

			if ($toolkit != null)
				$this->metadata = $tk->getMetadata($this);

		}

		return $this->metadata;

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
		$metadata = $this->getMetadata();
		if ($metadata)
			return $metadata[pixotic_MediaToolkit::$METADATA_DESCRIPTION];
		return null;
	}

}

