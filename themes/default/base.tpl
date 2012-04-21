<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?= $title; ?> - Pixotic</title>
		<link rel="stylesheet" type="text/css" href="<?= $pixotic->getThemeURL('css/style.css'); ?>"/>
		<script language="javascript" src="<?= $pixotic->getThemeURL('js/common.js'); ?>"></script>
	</head>
	<body>
		<div id="pageBody">
			<div id="pageHeader">
				<h1><?= $pixotic->getConfig('siteName', 'Pixotic'); ?></h1>
			</div>
			<div id="pageSidebar">
				<ul class="albumList">
				<?
				$albums = $pixotic->getAlbumNavigation($album);
				foreach ($albums as $a) {
					$pixotic->showBlock('albumNav.tpl', array('album' => $a));
				} ?>
				</ul>
			</div>
			<div id="pageContent">
				<?= $content; ?>
			</div>
			<div id="pageFooter">
				Powered by Pixotic
			</div>
		</div>
	</body>
</html>
