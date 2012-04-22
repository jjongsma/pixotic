<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$forward = $_REQUEST['forward'];
		if ($pixotic->login($_REQUEST['username'], $_REQUEST['password'])) {
			header('Location: '.$forward);
		} else {
			$pixotic->showPage('login.tpl', array('title' => 'Administration Login',
				'forward' => $forward,
				'failed' => true));
		}
	} else {
		$pixotic->showPage('login.tpl', array(
			'title' => 'Administration Login',
			'forward' => $_SERVER['HTTP_REFERER']));
	}
