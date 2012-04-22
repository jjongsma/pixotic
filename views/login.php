<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if ($pixotic->login($_REQUEST['username'], $_REQUEST['password'])) {
			header('Location: '.$pixotic->getRealURL('/'));
		} else {
			$pixotic->showPage('login.tpl', array('title' => 'Administration Login',
				'failed' => true));
		}
	} else {
		$pixotic->showPage('login.tpl', array('title' => 'Administration Login'));
	}
