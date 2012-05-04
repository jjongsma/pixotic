<?php

$id = $_REQUEST['id'];
$item = $pixotic->getItem($id);

if ($item) {

	$resized = new Pixotic_ResizedImage($source, $size,
		$pixotic->getConfig('cacheDirectory'));
	$resized->send();

}
