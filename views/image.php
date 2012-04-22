<?php

	$id = $_REQUEST['id'];

	$image = $id ? $image = $pixotic->getImage($id) : null;

	if ($image) {
	
		$pixotic->showPage('image.tpl',
			array('title' => $image->getName(),
				'active' => $image->getAlbum()->getRelPath(),
				'imageSize' => $pixotic->getConfig('imageSize', 640),
				'image' => $image));

	} else {

		$pixotic->showPage('notfound.tpl',
			array('title' => 'Album Not Found',
				'error' => 'The album <b>'.basename($id).'</b> was not found.'));

	}
