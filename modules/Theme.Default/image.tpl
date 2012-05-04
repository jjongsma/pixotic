<? if ($pixotic->getConfig('gallery.downloadFullSize', false) || $pixotic->isAdmin()) { ?>
<div class="fullsize">
	<a href="<?= $pixotic->getRealURL('/index.php?view=fullsize&amp;id='
		.rawurlencode($item->getID())); ?>">Download original size
		(<?= $item->getWidth(); ?>x<?= $item->getHeight(); ?>)</a>
</div>
<? } ?>

<h2>
<?
function getParent($album, $current = true) {
	global $pixotic;
	if ($album->getAlbum()) {
		echo getParent($album->getAlbum(), false);
		$link = $pixotic->getRealURL('/index.php?view=album&amp;id='.rawurlencode($album->getID()));
		return '<a href="'.$link.'">'.$album->getName().'</a> / ';
	}
}
echo getParent($item->getAlbum());
echo $item->getName();
?>
</h2>

<img class="resized" src="<?= $pixotic->getRealURL('/index.php?view=resized&amp;size='.
	$pixotic->getConfig('gallery.imageSize', 800).'&amp;id='.rawurlencode($item->getID())); ?>" />

<? if ($item->getDescription() || $pixotic->isAdmin()) { ?>
	<div id="imageDescription_<?= base64_encode($item->getID()); ?>" class="inlineDescription indent">
		<?= $item->getDescription(); ?>
		<? if ($pixotic->isAdmin()) { ?>
			<i>Click to add description</i>
		<? } ?>
	</div>
<? } ?>

<? if ($exifData = $pixotic->getMetadata($item)) { ?>
	<h3>Photo Details</h3>
	<table class="imageData">
		<tr>
			<td class="fieldName">Camera Make</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_CAMERA_MAKE]; ?></td>
			<td class="fieldName">Flash</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_FLASH]; ?></td>
		</tr>
		<tr>
			<td class="fieldName">Camera Model</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_CAMERA_MODEL]; ?></td>
			<td class="fieldName">Color Space</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_COLOR_SPACE]; ?></td>
		</tr>
		<tr>
			<td class="fieldName">Aperture</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_APERTURE]; ?></td>
			<td class="fieldName">Exposure Bias</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_EXPOSURE_BIAS]; ?></td>
		</tr>
		<tr>
			<td class="fieldName">Shutter Speed</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_SHUTTER]; ?></td>
			<td class="fieldName">Exposure Program</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_EXPOSURE_PROGRAM]; ?></td>
		</tr>
		<tr>
			<td class="fieldName">ISO</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_ISO]; ?></td>
			<td class="fieldName">Metering Mode</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_METERING]; ?></td>
		</tr>
		<tr>
			<td class="fieldName">Focal Length</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_FOCAL_LENGTH]; ?></td>
			<td class="fieldName">Date Taken</td>
			<td class="fieldValue"><?= $exifData[pixotic_MediaToolkit::$METADATA_DATE]; ?></td>
		</tr>
	</table>
<? } ?>
