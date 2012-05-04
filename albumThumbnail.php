<?php

$id = $_REQUEST['id'];
$item = $pixotic->getItem($id);

if ($item) {

	if (is_dir($source)) {

		// Folder icon from: http://findicons.com/icon/92220/black_folder
		$image = $pixotic->getRealPath('themes/'
			.$pixotic->getConfig('theme', 'default').'/images/folder_icon.png');
		$album = $pixotic->getAlbum($id);
		$resized = new Pixotic_AlbumIcon($image, $album, $size,
			$pixotic->getConfig('cacheDirectory'));
		$resized->send();

	} else {

		$resized = new Pixotic_ResizedImage($source, $size,
			$pixotic->getConfig('cacheDirectory'));
		$resized->send();

	}

}
