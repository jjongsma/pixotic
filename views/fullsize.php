<?php
$id = $_REQUEST['id'];
$item = $pixotic->getItem($id);
if ($item) {
	$file = $item->getAbsolutePath();
	if ($file)
		$pixotic->sendFile($file, true);
} else {
	header('Status: 404 Not Found');
}
