<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?= $title; ?> - Pixotic</title>
		<link rel="stylesheet" type="text/css" href="<?= $pixotic->getThemeURL('media/css/style.css'); ?>"/>
		<script language="javascript" src="<?= $pixotic->getThemeURL('media/js/common.js'); ?>"></script>
	</head>
	<body>
		<div class="albumList">
			<ul class="albums">
			<?
			$albums = $pixotic->getAlbumNavigation($album);
			foreach ($albums as $a) {
				$pixotic->showBlock('albumNav.tpl', array('album' => $a));
			} ?>
			</ul>
		</div>
		<div class="pageContent">
			<?= $content; ?>
		</div>
	</body>
</html>
