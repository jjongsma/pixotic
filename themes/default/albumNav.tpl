<li<?= $album['selected'] ? ' class="selected"' : ''; ?>><a
	<? if ($level > 2) echo 'style="padding-left: '.(($level - 2) * 10).'px"'; ?>
	href="<?= $pixotic->getRealURL('/index.php?view=album&amp;id='.rawurlencode($album['path'])); ?>"><?= $album['name']; ?></a></li>
<? if (count($album['albums']) > 0) { ?>
	<ul>
	<? foreach ($album['albums'] as $a) {
		$this->showBlock('albumNav.tpl', array('album' => $a, 'level' => $level + 1));
	} ?>
	</ul>
<? } ?>
