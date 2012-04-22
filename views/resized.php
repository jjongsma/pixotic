<?php

$id = $_REQUEST['id'];
$source = $pixotic->getConfig('albumDirectory').'/'.$id;
$size = $_REQUEST['size'];

if (file_exists($source)) {

	if (is_dir($source)) {

		// Folder icon from: http://findicons.com/icon/92220/black_folder
		$image = $pixotic->getRealPath('themes/'
			.$pixotic->getConfig('theme', 'default').'/images/folder_icon.png');
		$resized = new Pixotic_ResizedImage($image, $size,
			$pixotic->getConfig('cacheDirectory'));
		$resized->send();

	} else {

		$resized = new Pixotic_ResizedImage($source, $size,
			$pixotic->getConfig('cacheDirectory'));
		$resized->send();

	}

}
