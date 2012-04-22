<?php
	require_once('lib/common.inc.php');

	$views = array(
		'album' => 'views/album.php',
		'image' => 'views/image.php',
		'resized' => 'views/resized.php',
		'fullsize' => 'views/fullsize.php',
		'login' => 'views/login.php',
		'logout' => 'views/logout.php',
		'manage' => 'views/manage.php',
	);

	$view = $_REQUEST['view'];
	if (!$view)
		$view = 'album';

	if (isset($views[$view])) {
		include($views[$view]);
	} else {
		include('views/error.php');
	}
