<li<?= $album['selected'] ? ' class="selected"' : ''; ?>><a href="/index.php?view=album&amp;id=<?= htmlentities($album['path']); ?>"><?= $album['name']; ?></a></li>
<? if (count($album['albums']) > 0) { ?>
	<ul class="albumList">
	<? foreach ($album['albums'] as $a) {
		$pixotic->showBlock('albumNav.tpl', array('album' => $a));
	} ?>
	</ul>
<? } ?>
