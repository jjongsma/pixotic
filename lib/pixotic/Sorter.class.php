<?php

// Sorting constants
define('SORT_CUSTOM', 'SORT_CUSTOM');
define('SORT_NAME', 'SORT_NAME');
define('SORT_NAME_DESCENDING', 'SORT_NAME_DESCENDING');
define('SORT_DATE', 'SORT_DATE');
define('SORT_DATE_DESCENDING', 'SORT_DATE_DESCENDING');
define('SORT_EXIF_DATE', 'SORT_EXIF_DATE');
define('SORT_EXIF_DATE_DESCENDING', 'SORT_EXIF_DATE_DESCENDING');

class pixotic_Sorter {

	private static $handlers = array(
		SORT_CUSTOM => 'sortByCustom',
		SORT_NAME => 'sortByName',
		SORT_NAME_DESCENDING => 'sortByNameDesc',
		SORT_DATE => 'sortByLastModified',
		SORT_DATE_DESCENDING => 'sortByLastModifiedDesc',
		SORT_EXIF_DATE => 'sortByExifDate',
		SORT_EXIF_DATE_DESCENDING => 'sortByExifDateDesc'
	);

	private static $cacheable = array(
		SORT_CUSTOM => 'custom',
		SORT_EXIF_DATE => 'exif',
		SORT_EXIF_DATE_DESCENDING => 'exifdesc'
	);

	private $pixotic;
	private $cache;
	private $log;
	private $exifCache;

	public function __construct($pixotic) {
		$this->pixotic = $pixotic;
		$this->cache = $pixotic->getService(pixotic_Service::$CACHE);
		$this->log = $pixotic->getLog();
		$this->exifCache = array();
	}

	// Usage contract: all items to be sorted must be of the same type and
	// in the same directory
	public function sort(&$items, $type = SORT_NAME) {

		if (!count($items))
			return $items;

		if ($this->cache && isset(pixotic_Sorter::$cacheable[$type])) {
			
			// Determine a unique ID for this sorted set
			$refitem = reset($items);
			$parent = $refitem->getAlbum();
			$parentId = $parent ?  $parent->getID() : 'ROOTALBUM';
			$itemType = $refitem instanceof pixotic_MediaItem ? 'item' : 'album';
			$cacheKey = 'sort-'.$parentId.'-'.$itemType.'-'.$type;
			
			$lastmod = $parent ? $parent->getLastModified() : 0;

			// Found cache, scan directory for last modified time to see if we need
			// to regenerate
			if ($this->cache->exists($cacheKey)) {
				foreach ($items as $i)
					if ($i->getLastModified() > $lastmod)
						$lastmod = $i->getLastModified();
			}

			$cachedSort = $this->cache->get($cacheKey, $lastmod);

			if (!$cachedSort) {
			
				usort($items, array($this, pixotic_Sorter::$handlers[$type]));
				$itemIDs = array();
				foreach ($items as $i)
					$itemIDs[] = $i->getID();
				$this->cache->put($cacheKey, implode("\n", $itemIDs));

			} else {
			
				// Sort like cache file
				$sorted = array();
				$order = array_flip(explode("\n", $cachedSort));

				foreach ($items as $f) {
					if (isset($order[$f->getID()])) {
						$sorted[$order[$f->getID()]] = $f;
						unset($order[$f->getID()]);
					}
				}

				ksort($sorted);
				return $sorted;

			}

		} else {

			usort($items, array($this, pixotic_Sorter::$handlers[$type]));

		}

		return $items;

	}

	public function sortByName($a, $b) {
		$f1 = basename($a->getID());
		$f2 = basename($b->getID());
		return strcmp($f1, $f2);
	}

	public function sortByLastModified($a, $b) {

		$m1 = $a->getLastModified();
		$m2 = $b->getLastModified();

		if ($m1 < $m2)
			return -1;
		else if ($m1 > $m2)
			return 1;

		return 0;

	}

	public function sortByExifDate($a, $b) {

		// TODO: special case albums, need to search sub-albums

		$e1 = $this->getExifDate($a->getAbsolutePath());
		$e2 = $this->getExifDate($b->getAbsolutePath());

		if ($e1 < $e2)
			return -1;
		else if ($e1 > $e2)
			return 1;

		return 0;

	}

	private function getExifDate($file) {

		if (!file_exists($file))
			return 0;

		if (is_dir($file))
			return filemtime($file);

		if (isset($this->exifCache[$file]))
			return $this->exifCache[$file];

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

		$this->exifCache[$file] = $exifDate;

		return $exifDate;

	}

	// Reverse versions

	public function sortByNameDesc($a, $b) {
		return $this->sortByName($b, $a);
	}

	public function sortByLastModifiedDesc($a, $b) {
		return $this->sortByLastModified($b, $a);
	}

	public function sortByExifDateDesc($a, $b) {
		return $this->sortByExifDate($b, $a);
	}

}
