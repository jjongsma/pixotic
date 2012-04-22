<?php
	$pixotic->logout();
	header('Location: '.$_SERVER['HTTP_REFERER']);
