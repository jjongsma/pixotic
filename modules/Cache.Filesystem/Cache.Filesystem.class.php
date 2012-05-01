<?php
require_once(PIXOTIC.'/lib/pixotic/Module.class.php');
require_once(PIXOTIC.'/lib/pixotic/Service.class.php');
require_once(PIXOTIC.'/lib/pixotic/Cache.class.php');

class pixotic_Cache_Filesystem implements pixotic_Module, pixotic_Cache {

	private $cacheDir;

	public function activate(&$pixotic) {
		$this->cacheDir = $pixotic->getConfig('cache.filesystem.directory');
		$pixotic->registerService(pixotic_Service::$CACHE, $this);
	}

	public function deactivate() {
	}

	public function get($key, $newerThan = null) {
		$cacheFile = $this->getCacheFile($key);
		if (!file_exists($cacheFile)
				|| ($newerThan && filemtime($cacheFile) < $newerThan))
			return null;
		return file_get_contents($cacheFile);
	}

	public function exists($key, $newerThan = null) {
		$cacheFile = $this->getCacheFile($key);
		if (!file_exists($cacheFile)
				|| ($newerThan && filemtime($cacheFile) < $newerThan))
			return false;
		return true;
	}

	public function put($key, $data) {
		$cacheFile = $this->getCacheFile($key);
		file_put_contents($cacheFile.'.tmp', $data, LOCK_EX);
		// Atomic commit
		rename($cacheFile.'.tmp', $cacheFile);
	}

	private function getCacheFile($key) {
		return $this->cacheDir.'/'.md5($key).'.cache';
	}

}
