<?php
	require_once('lib/common.inc.php');

	$view = $_REQUEST['view'];
	$id = $_REQUEST['id'];

	switch ($view) {
		case 'image':
		case 'album':
		default:
			if (!$id) {
				$default = $pixotic->getDefaultAlbum();
				if ($default)
					$id = $default->getRelPath();
			}
			$pixotic->showPage('albumOverview.tpl',
				array('title' => 'All Albums', 'album' => $id));
	}
