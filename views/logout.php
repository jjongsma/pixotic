<?php
	$pixotic->logout();
	header('Location: '.$pixotic->getRealURL('/'));
