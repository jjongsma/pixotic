<li<?= $album['selected'] ? ' class="selected"' : ''; ?>><a href="/index.php?view=album&amp;id=<?= rawurlencode($album['path']); ?>"><?= $album['name']; ?></a></li>
<? if (count($album['albums']) > 0) { ?>
	<ul>
	<? foreach ($album['albums'] as $a) {
		$this->showBlock('albumNav.tpl', array('album' => $a));
	} ?>
	</ul>
<? } ?>
