<?php
$id = $_REQUEST['id'];
$item = $pixotic->getItem($id);
if ($item) {
	$file = $pixotic->getItemPreview($item, $pixotic->getConfig('gallery.imageSize'));
	if ($file)
		$pixotic->sendFile($file);
}
