<?php

	$id = $_REQUEST['id'];

	$item = $id ? $item = $pixotic->getItem($id) : null;

	if ($item) {
	
		$pixotic->showPage('image.tpl',
			array('title' => $item->getName(),
				'active' => $item->getAlbum()->getID(),
				'item' => $item));

	} else {

		$pixotic->showPage('notfound.tpl',
			array('title' => 'Item Not Found',
				'error' => 'The item <b>'.$id.'</b> was not found.'));

	}
