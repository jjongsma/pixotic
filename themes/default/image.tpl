<h2><?= $image->getName(); ?></h2>

<img class="resized" src="<?= $pixotic->getRealURL('index.php?view=resized&amp;size='.
	$pixotic->getConfig('imageSize', 800).'&amp;id='.rawurlencode($image->getRelPath())); ?>" />

<? if ($exifData = $image->getExifData()) { ?>
<h3>Photo Details</h3>
<table class="imageData">
	<tr>
	<?
	$i = 0;
	foreach ($exifData as $name => $value) { ?>
		<td class="fieldName"><?= $name; ?></td>
		<td class="fieldValue"><?= $value; ?></td>
	<? if ($i++ % 2 == 1) { ?>
		</tr><tr>
		<? }
	} ?>
	</tr>
</table>
<? } ?>
