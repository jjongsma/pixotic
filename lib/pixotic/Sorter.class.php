<?php

// Sorting constants
define('CUSTOM', 'CUSTOM');
define('FILENAME', 'FILENAME');
define('FILENAME_DESCENDING', 'FILENAME_DESCENDING');
define('FILE_DATE', 'FILE_DATE');
define('FILE_DATE_DESCENDING', 'FILE_DATE_DESCENDING');
define('EXIF_DATE', 'EXIF_DATE');
define('EXIF_DATE_DESCENDING', 'EXIF_DATE_DESCENDING');

class pixotic_Sorter {

	private static $handlers = array(
		CUSTOM => 'SortByCustom',
		FILENAME => 'SortByFilename',
		FILENAME_DESCENDING => 'SortByFilenameDesc',
		FILE_DATE => 'SortByFileDate',
		FILE_DATE_DESCENDING => 'SortByFileDateDesc',
		EXIF_DATE => 'SortByExifDate',
		EXIF_DATE_DESCENDING => 'SortByExifDateDesc'
	);

	private static $cacheable = array(
		CUSTOM => 'custom',
		EXIF_DATE => 'exif',
		EXIF_DATE_DESCENDING => 'exifdesc'
	);

	private static $exifCache = array();

	// Usage contract: all items to be sorted must be of the same type and
	// in the same directory
	public static function Sort(&$ary, $type = FILENAME, $cacheDir = null) {

		if (!count($ary))
			return $ary;

		$refitem = reset($ary);
		$dir = dirname($refitem->getPath());

		// EXIF not supported for directories
		if ($refitem instanceof pixotic_Album) {
			if ($type == EXIF_DATE)
				$type = FILE_DATE;
			elseif ($type == EXIF_DATE_DESCENDING)
				$type = FILE_DATE_DESCENDING;
		}

		$sortCache = null;
		$refreshCache = false;
		pixotic_Sorter::$exifCache = array();

		if (isset(pixotic_Sorter::$cacheable[$type])) {

			$cacheKey = md5($dir.'-'.$type);
			$sortCache = $cacheDir.'/'.pixotic_Sorter::$cacheable[$type].'sort-'.$cacheKey;

			if (file_exists($sortCache)) {

				$cacheTime = filemtime($sortCache);

				if (filemtime($dir) > $cacheTime) {
					$refreshCache = true;
				} else {
					$dh = opendir($dir);
					while ($f = readdir($dh)) {
						if (filemtime($dir.'/'.$f) > $cacheTime) {
							$refreshCache = true;
							break;
						}
					}
					closedir($dh);
				}

			} else {
				$refreshCache = true;
			}

			if ($refreshCache) {

				usort($ary, array('pixotic_Sorter', pixotic_Sorter::$handlers[$type]));
				pixotic_Sorter::CacheSortOrder($sortCache, $ary);

			} else {
			
				// Sort like cache file
				$sorted = array();
				$order = array_flip(explode("\n", trim(file_get_contents($sortCache))));

				foreach ($ary as $f) {
					$sorted[$order[$f->getPath()]] = $f;
					unset($order[$f->getPath()]);
				}

				ksort($sorted);

				// Add any missing items
				if (count($order) > 0) {
					$sorted = array_merge($sorted, array_flip($order));
					pixotic_Sorter::CacheSortOrder($sortCache, $sorted);
				}

				return $sorted;

			}

		} else {

			usort($ary, array('pixotic_Sorter', pixotic_Sorter::$handlers[$type]));

		}

		return $ary;

	}

	private static function CacheSortOrder($cacheFile, &$items) {

		if (is_dir(dirname($cacheFile))) {

			$fh = fopen($cacheFile.'.tmp', 'w');

			if (flock($fh, LOCK_EX)) {
				foreach ($items as $f)
					fwrite($fh, $f->getPath()."\n");
				fflush($fh);
				flock($fh, LOCK_UN);
			}

			fclose($fh);

			rename($cacheFile.'.tmp', $cacheFile);

		}

	}

	public static function SortByFilename($a, $b) {
		$f1 = basename($a->getPath());
		$f2 = basename($b->getPath());
		return strcmp($f1, $f2);
	}

	public static function SortByFileDate($a, $b) {

		$f1 = $a->getPath();
		$f2 = $b->getPath();

		if (!file_exists($f1)) {
			if (file_exists($f2))
				return -1;
			return 0;
		}


		if (!file_exists($f2))
			return 1;
		$m1 = filemtime($f1);
		$m2 = filemtime($f2);

		if ($m1 < $m2)
			return -1;
		else if ($m1 > $m2)
			return 1;

		return 0;

	}

	public static function SortByExifDate($a, $b) {

		$e1 = pixotic_Sorter::GetExifDate($a->getPath());
		$e2 = pixotic_Sorter::GetExifDate($b->getPath());

		if ($e1 < $e2)
			return -1;
		else if ($e1 > $e2)
			return 1;

		return 0;

	}

	private static function GetExifDate($file) {

		if (!file_exists($file))
			return 0;

		if (is_dir($file))
			return filemtime($file);

		if (isset(pixotic_Sorter::$exifCache[$file]))
			return pixotic_Sorter::$exifCache[$file];

		$exif = exif_read_data($file);

		if (!$exif)
			return filemtime($file);

		$exifDate = null;
		foreach (array('DateTimeOriginal', 'DateTimeDigitized', 'DateTime', 'FileDateTime') as $tag) {
			if (isset($exif[$tag])) {
				$exifDate = strtotime($exif[$tag]);
				break;
			}
		}

		if (!$exifDate)
			return filemtime($file);

		pixotic_Sorter::$exifCache[$file] = $exifDate;

		return $exifDate;

	}

	// Reverse versions

	public static function SortByFilenameDesc($a, $b) {
		return pixotic_Sorter::SortByFilename($b, $a);
	}

	public static function SortByFileDateDesc($a, $b) {
		return pixotic_Sorter::SortByFileDate($b, $a);
	}

	public static function SortByExifDateDesc($a, $b) {
		return pixotic_Sorter::SortByExifDate($b, $a);
	}

}
