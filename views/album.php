<?php

	$id = $_REQUEST['id'];

	if (!$id) {
		$default = $pixotic->getDefaultAlbum();
		if ($default)
			$id = $default->getID();
	}

	$album = $id ? $album = $pixotic->getAlbum($id) : null;

	if ($album) {

		$page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;
		$items = array_merge($album->getAlbums(), $album->getItems());
		$perpage = 10 * 5; // rows * cols
		$pages = ceil(count($items) / $perpage);

		if ($page > $pages)
			$page = $pages;

		$start = $perpage * ($page - 1);
		$length = $perpage;
		if ($start + $length > count($items))
			$end = count($items) - $start;

		$items = array_slice($items, $start, $length);

		$pixotic->showPage('album.tpl',
			array('title' => $album->getName(),
				'active' => $id,
				'thumbnailSize' => $pixotic->getConfig('gallery.thumbnailSize', 128),
				'page' => $page,
				'pages' => $pages,
				'items' => $items,
				'album' => $album));

	} else {

		$pixotic->showPage('notfound.tpl',
			array('title' => 'Album Not Found',
				'error' => 'The album <b>'.basename($id).'</b> was not found.'));

	}
