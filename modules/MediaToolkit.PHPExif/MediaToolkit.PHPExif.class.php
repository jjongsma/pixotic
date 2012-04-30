<?php
require_once(PIXOTIC.'/lib/pixotic/Module.class.php');
require_once(PIXOTIC.'/lib/pixotic/MediaToolkit.class.php');

class pixotic_MediaToolkit_PHPExif extends pixotic_MediaToolkit implements pixotic_Module {

	public function activate(&$pixotic) {
		$pixotic->registerService(pixotic_Service::$MEDIA_TOOLKIT, $this);
	}

	public function deactivate() {
	}

	public function getSupportedMimeTypes() {
		return array(
			'image/jpeg'
		);
	}

	public function getSupportedActions() {
		return array(
			pixotic_MediaToolkit::$ACTION_READ_METADATA
		);
	}

	private function computeExifValue($val) {

		if ($val) {
			$tmp = explode('/', $val);
			if (count($tmp) == 2)
				return $tmp[0] / $tmp[1];
		}

		return $val;

	}

	public function getMetadata($mediaItem) {

		$exif = exif_read_data($mediaItem->getAbsolutePath());

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
				pixotic_MediaToolkit::$METADATA_CAMERA_MAKE => $exif['Make'],
				pixotic_MediaToolkit::$METADATA_CAMERA_MODEL => $exif['Model'],
				pixotic_MediaToolkit::$METADATA_APERTURE => $aperture,
				pixotic_MediaToolkit::$METADATA_SHUTTER => $shutter,
				pixotic_MediaToolkit::$METADATA_ISO => $exif['ISOSpeedRatings'],
				pixotic_MediaToolkit::$METADATA_FOCAL_LENGTH => $focal,
				pixotic_MediaToolkit::$METADATA_FLASH => $exif['Flash'] ? 'Flash' : 'No Flash',
				pixotic_MediaToolkit::$METADATA_COLOR_SPACE => $exif['ColorSpace'] == 1 ? 'sRGB' : 'Unknown',
				pixotic_MediaToolkit::$METADATA_EXPOSURE_BIAS => $expbias,
				pixotic_MediaToolkit::$METADATA_EXPOSURE_PROGRAM => $expmode,
				pixotic_MediaToolkit::$METADATA_METERING => $metermode,
				pixotic_MediaToolkit::$METADATA_DATE => $exif['DateTimeOriginal'],
				pixotic_MediaToolkit::$METADATA_DESCRIPTION => $exif['ImageDescription']
					? trim($exif['ImageDescription'])
					: trim($exif['COMPUTED']['UserComment'])
			);

			return $data;

		}

		return null;

	}

	public function scaleItem($mediaItem, $ratio, $outFile) {
		throw new Exception('Unsupported action');
	}

	public function resizeItem($mediaItem, $width, $height, $outFile, $cropX = 0, $cropY = 0, $cropWidth = 0, $cropHeight = 0) {
		throw new Exception('Unsupported action');
	}

	public function writeMetadata($mediaItem, $metadata) {
		throw new Exception('Unsupported action');
	}

	public function createAlbumThumbnail($album, $width, $height, $format, $outFile) {
		throw new Exception('Unsupported action');
	}

}

