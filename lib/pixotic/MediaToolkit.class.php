<?php

abstract class pixotic_MediaToolkit {

	public static $ACTION_RESIZE = 'ACTION_RESIZE';
	public static $ACTION_ROTATE = 'ACTION_ROTATE';
	public static $ACTION_READ_METADATA = 'ACTION_READ_METADATA';
	public static $ACTION_WRITE_METADATA = 'ACTION_WRITE_METADATA';
	public static $ACTION_ALBUM_THUMBNAIL = 'ACTION_ALBUM_THUMBNAIL';

	public static $METADATA_CAMERA_MAKE = 'METADATA_CAMERA_MAKE';
	public static $METADATA_CAMERA_MODEL = 'METADATA_CAMERA_MODEL';
	public static $METADATA_APERTURE = 'METADATA_APERTURE';
	public static $METADATA_SHUTTER = 'METADATA_SHUTTER';
	public static $METADATA_FOCAL_LENGTH = 'METADATA_FOCAL_LENGTH';
	public static $METADATA_ISO = 'METADATA_ISO';
	public static $METADATA_FLASH = 'METADATA_FLASH';
	public static $METADATA_EXPOSURE_BIAS = 'METADATA_EXPOSURE_BIAS';
	public static $METADATA_EXPOSURE_PROGRAM = 'METADATA_EXPOSURE_PROGRAM';
	public static $METADATA_METERING = 'METADATA_METERING';
	public static $METADATA_COLOR_SPACE = 'METADATA_COLOR_SPACE';
	public static $METADATA_DATE = 'METADATA_DATE';
	public static $METADATA_TITLE = 'METADATA_TITLE';
	public static $METADATA_DESCRIPTION = 'METADATA_DESCRIPTION';
	public static $METADATA_HEIGHT = 'METADATA_HEIGHT';
	public static $METADATA_WIDTH = 'METADATA_WIDTH';
	public static $METADATA_CODEC = 'METADATA_CODEC';
	public static $METADATA_CONTAINER = 'METADATA_CONTAINER';
	public static $METADATA_FPS = 'METADATA_FPS';

	public static $FORMAT_JPEG = 'FORMAT_JPEG';
	public static $FORMAT_PNG = 'FORMAT_PNG';
	public static $FORMAT_GIF = 'FORMAT_GIF';

	/**
	 * Return a list of supported media mime types that this toolkit can
	 * operate on.
	 * @return Array A string array of mime types
	 */
	public abstract function getSupportedMimeTypes();

	/**
	 * Return a list of supported actions that this toolkit can operate on.
	 * @return Array An array of MediaToolkit::ACTION_* constants.
	 */
	public abstract function getSupportedActions();

	/**
	 * Scale the media item by a fixed ratio and write the output to the
	 * specified file.
	 * @param pixotic_MediaItem The media item
	 * @param float $ratio The scaling ratio
	 * @param $outFile The output file to write to
	 */
	public abstract function scaleItem($mediaItem, $ratio, $outFile);

	/**
	 * Resize the media item to the specified width and height, and optionally
	 * cropping, writing to the specified file.
	 * @param pixotic_MediaItem The media item
	 * @param int $width The output width
	 * @param int $height The output height
	 * @param $outFile The output file to write to
	 * @param $cropX The crop X coordinate
	 * @param $cropY The crop Y coordinate
	 * @param $cropWidth The crop width
	 * @param $cropHeight The crop height
	 */
	public abstract function resizeItem($mediaItem, $width, $height, $outFile, $cropX = 0, $cropY = 0, $cropWidth = 0, $cropHeight = 0);

	/**
	 * Read metadata from the media item and return it as an array.  Keys should
	 * use pixotic_MediaToolkit::$METADATA_* constants when applicable.
	 * @param pixotic_MediaItem The media item
	 */
	public abstract function getMetadata($mediaItem);

	/**
	 * Write the specified metadata to the given media item.
	 * @param pixotic_MediaItem $mediaItem The media item
	 * @param Array $metadata The metadata values, using pixotic_MediaToolkit::$METADATA_*
	 *		keys when applicable.
	 */
	public abstract function writeMetadata($mediaItem, $metadata);

	/**
	 * Generate an album thumbnail image for the specified album.
	 * @param pixotic_Album $album The album to thumbnail
	 * @param int $width The thumbnail width
	 * @param int $height The thumbnail height
	 * @param string $format The preferred image format to write to (see
	 *		pixotic_MediaToolkit::$FORMAT_*)
	 * @param string $outFile The file to write the thumbnail to.
	 */
	public abstract function createAlbumThumbnail($album, $width, $height, $format, $outFile);

}
