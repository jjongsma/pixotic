<?php

	$id = $_REQUEST['id'];

	if (!$id) {
		$default = $pixotic->getDefaultAlbum();
		if ($default)
			$id = $default->getRelPath();
	}

	$album = $id ? $album = $pixotic->getAlbum($id) : null;

	if ($album) {

		$page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;
		$items = array_merge($album->getAlbums(), $album->getImages());
		$perpage = $pixotic->getConfig('albumRows', 5) * $pixotic->getConfig('albumCols', 5);
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
				'thumbnailSize' => $pixotic->getConfig('thumbnailSize', 128),
				'page' => $page,
				'pages' => $pages,
				'items' => $items,
				'album' => $album));

	} else {

		$pixotic->showPage('notfound.tpl',
			array('title' => 'Album Not Found',
				'error' => 'The album <b>'.basename($id).'</b> was not found.'));

	}
