<?php
$id = $_REQUEST['id'];
$album = $pixotic->getAlbum($id);
if ($album)
	$pixotic->sendFile($pixotic->getAlbumThumbnail($album));
