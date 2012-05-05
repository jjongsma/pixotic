<?php
$id = $_REQUEST['id'];
$item = $pixotic->getItem($id);
if ($item)
	$pixotic->sendFile($pixotic->getItemThumbnail($item));
