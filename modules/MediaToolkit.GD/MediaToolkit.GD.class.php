<?php
require_once(PIXOTIC.'/lib/pixotic/Module.class.php');
require_once(PIXOTIC.'/lib/pixotic/MediaToolkit.class.php');
require_once('ResizedImage.class.php');
require_once('AlbumThumbnail.class.php');

class pixotic_MediaToolkit_GD extends pixotic_MediaToolkit implements pixotic_Module {

	public function activate(&$pixotic) {
		$pixotic->registerService(pixotic_Service::$MEDIA_TOOLKIT, $this);
	}

	public function deactivate() {
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

	public function scaleItem($mediaItem, $ratio, $outFile) {
	}

	public function resizeItem($mediaItem, $width, $height, $outFile, $cropX = 0, $cropY = 0, $cropWidth = 0, $cropHeight = 0) {
	}

	public function getMetadata($mediaItem) {
		throw new Exception('Unsupported action');
	}

	public function writeMetadata($mediaItem, $metadata) {
		throw new Exception('Unsupported action');
	}

	public function createAlbumThumbnail($album, $width, $height, $format, $outFile) {
	}

}
