<?php
$image = $pixotic->getItem($_REQUEST['id']);
if ($image)
	$image->getFullSize()->send(true);
else
	header('Status: 404 Not Found');
