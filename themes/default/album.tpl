<div class="pageList">
Page:
<? foreach (range(1, $pages) as $p) {
	if ($p == $page) { ?>
		<?= $p; ?>
	<? } else { ?>
		<a href="<?= $pixotic->getRealURL('index.php?view=album&amp;id='.rawurlencode($album->getRelPath()).'&amp;page='.$p); ?>"><?= $p; ?></a>
	<? }
} ?>
</div>

<h2><?
function getParent($album, $current = true) {
	global $pixotic;
	if ($album->getParent()) {
		echo getParent($album->getParent(), false);
		if ($current)
			return $album->getName();
		else {
			$link = $pixotic->getRealURL('index.php?view=album&amp;id='.rawurlencode($album->getRelPath()));
			return '<a href="'.$link.'">'.$album->getName().'</a> / ';
		}
	}
}
echo getParent($album);
?>
</h2>

<table class="thumbnails">
	<tr>
	<? for ($i = 0; $i < count($items); $i++) {
		$cols = $pixotic->getConfig('albumCols', 5);
		$item = $items[$i];
		$view = $item instanceof Pixotic_Album ? 'album' : 'image';
		?>
		<td width="<?= floor(100 / $cols); ?>%">
			<a href="<?= $pixotic->getRealURL('index.php?view='.$view.'&amp;id='.rawurlencode($item->getRelPath())); ?>">
				<img class="<?= $item instanceof Pixotic_Album ? 'album' : 'thumbnail'; ?>" src="<?= $pixotic->getRealURL('index.php?view=resized&amp;size='.
					$pixotic->getConfig('thumbnailSize', 128).'&amp;id='.rawurlencode($item->getRelPath())); ?>" />
				<br />
				<?= $item->getName(); ?>
			</a>
		</td>
		<? if ($i % $cols == ($cols - 1) && $i != count($items) - 1) { ?>
			</tr>
			<tr>
		<? }
	}
	if ($i % $cols < 0) {
		foreach (range($i % $cols, $cols -1) as $x) {
			echo '<td width="'.floor(100/$cols).'%"></td>';
		}
	}
	?>
	</tr>
</table>

<div class="pageList">
Page:
<? foreach (range(1, $pages) as $p) {
	if ($p == $page) { ?>
		<?= $p; ?>
	<? } else { ?>
		<a href="<?= $pixotic->getRealURL('index.php?view=album&amp;id='.rawurlencode($album->getRelPath()).'&amp;page='.$p); ?>"><?= $p; ?></a>
	<? }
} ?>
</div>
