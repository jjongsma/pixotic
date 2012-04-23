<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?= $title; ?> - Pixotic</title>
		<link rel="stylesheet" type="text/css" href="<?= $this->getThemeURL('css/style.css'); ?>"/>
		<script language="javascript" src="<?= $this->getThemeURL('js/common.js'); ?>"></script>
	</head>
	<body>
		<div id="pageBody">
			<div id="pageHeader">
				<div class="logo">
					<a href="<?= $pixotic->getRealURL('/'); ?>"><img
						src="<?= $pixotic->getRealURL('/images/pixotic-icon.png'); ?>"/></a>
				</div>
				<h1><a href="<?= $pixotic->getRealURL('/'); ?>"><?= $pixotic->getConfig('siteName', 'Pixotic'); ?></a></h1>
			</div>
			<div id="pageSidebar">
				<ul class="albumList">
				<?
				$albums = $this->getAlbumNavigation($active);
				foreach ($albums as $a) {
					$this->showBlock('albumNav.tpl', array('album' => $a, 'level' => 1));
				} ?>
				</ul>
				<? if ($pixotic->isLoggedIn()) { ?>
					<div class="manage">
						<h4>Gallery Management</h4>
						<ul>
							<li><a href="<?= $pixotic->getRealURL('/index.php?view=manage'); ?>">Gallery Management</a></li>
							<li><a href="<?= $pixotic->getRealURL('/index.php?view=logout'); ?>">Logout</a></li>
						</ul>
					</div>
				<? } else { ?>
					<a class="login" href="<?= $pixotic->getRealURL('/index.php?view=login'); ?>">Admin Login</a>
				<? } ?>
			</div>
			<div id="pageContent">
				<?= $content; ?>
			</div>
			<div id="pageFooter">
				Powered by <a target="_blank" href="http://jongsma.org/software/pixotic">Pixotic</a>
			</div>
		</div>
	</body>
</html>
