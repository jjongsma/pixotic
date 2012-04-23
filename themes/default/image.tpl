<? if ($pixotic->getConfig('downloadFullSize', false) || $pixotic->isAdmin()) { ?>
<div class="fullsize">
	<a href="<?= $pixotic->getRealURL('/index.php?view=fullsize&amp;id='
		.rawurlencode($image->getRelPath())); ?>">Download original size
		(<?= $image->getWidth(); ?>x<?= $image->getHeight(); ?>)</a>
</div>
<? } ?>

<h2>
<?
function getParent($album, $current = true) {
	global $pixotic;
	if ($album->getParent()) {
		echo getParent($album->getParent(), false);
		$link = $pixotic->getRealURL('/index.php?view=album&amp;id='.rawurlencode($album->getRelPath()));
		return '<a href="'.$link.'">'.$album->getName().'</a> / ';
	}
}
echo getParent($image->getAlbum());
echo $image->getName();
?>
</h2>

<img class="resized" src="<?= $pixotic->getRealURL('/index.php?view=resized&amp;size='.
	$pixotic->getConfig('imageSize', 800).'&amp;id='.rawurlencode($image->getRelPath())); ?>" />

<? if ($image->getDescription() || $pixotic->isAdmin()) { ?>
	<div id="imageDescription_<?= base64_encode($image->getRelPath()); ?>" class="inlineDescription indent">
		<?= $image->getDescription(); ?>
		<? if ($pixotic->isAdmin()) { ?>
			<i>Click to add description</i>
		<? } ?>
	</div>
<? } ?>

<? if ($exifData = $image->getExifData()) { ?>
	<h3>Photo Details</h3>
	<table class="imageData">
		<tr>
			<td class="fieldName">Camera Make</td>
			<td class="fieldValue"><?= $exifData['Camera Make']; ?></td>
			<td class="fieldName">Flash</td>
			<td class="fieldValue"><?= $exifData['Flash']; ?></td>
		</tr>
		<tr>
			<td class="fieldName">Camera Model</td>
			<td class="fieldValue"><?= $exifData['Camera Model']; ?></td>
			<td class="fieldName">Color Space</td>
			<td class="fieldValue"><?= $exifData['Color Space']; ?></td>
		</tr>
		<tr>
			<td class="fieldName">Aperture</td>
			<td class="fieldValue"><?= $exifData['Aperture']; ?></td>
			<td class="fieldName">Exposure Bias</td>
			<td class="fieldValue"><?= $exifData['Exposure Bias']; ?></td>
		</tr>
		<tr>
			<td class="fieldName">Shutter Speed</td>
			<td class="fieldValue"><?= $exifData['Shutter Speed']; ?></td>
			<td class="fieldName">Exposure Program</td>
			<td class="fieldValue"><?= $exifData['Exposure Program']; ?></td>
		</tr>
		<tr>
			<td class="fieldName">ISO</td>
			<td class="fieldValue"><?= $exifData['ISO']; ?></td>
			<td class="fieldName">Metering Mode</td>
			<td class="fieldValue"><?= $exifData['Metering Mode']; ?></td>
		</tr>
		<tr>
			<td class="fieldName">Focal Length</td>
			<td class="fieldValue"><?= $exifData['Focal Length']; ?></td>
			<td class="fieldName">Date Taken</td>
			<td class="fieldValue"><?= $exifData['Date Taken']; ?></td>
		</tr>
	</table>
<? } ?>
