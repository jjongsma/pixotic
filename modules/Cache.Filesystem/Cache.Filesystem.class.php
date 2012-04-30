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

	public function get($key, $expiration = null) {
		$cacheFile = $this->getCacheFile($key);
		if (!file_exists($cacheFile)
				|| ($expiration && filemtime($cacheFile) < ($expiration + time())))
			return null;
		return file_get_contents($cacheFile);
	}

	public function put($key, $data) {
		file_put_contents($this->getCacheFile($key), $data, LOCK_EX);
	}

	private function getCacheFile($key) {
		return $this->cacheDir.'/'.md5($key).'.cache';
	}

}
