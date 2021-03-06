<?php
require_once(PIXOTIC.'/lib/pixotic/Module.class.php');
require_once(PIXOTIC.'/lib/pixotic/Service.class.php');
require_once(PIXOTIC.'/lib/pixotic/Cache.class.php');

class pixotic_Cache_Filesystem implements pixotic_Module, pixotic_Cache {

	private $log;
	private $cacheDir;

	public function activate(&$pixotic) {
		$this->log = $pixotic->getLog();
		$this->cacheDir = $pixotic->getConfig('cache.filesystem.directory');
		$pixotic->registerService(pixotic_Service::$CACHE, $this);
	}

	public function deactivate() {
	}

	public function get($key, $newerThan = null) {
		$cacheFile = $this->getCacheFile($key);
		if (!file_exists($cacheFile)
				|| ($newerThan && filemtime($cacheFile) < $newerThan)) {
			return null;
		}
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

	public function invalidate($key) {
		$cacheFile = $this->getCacheFile($key);
		if (file_exists($cacheFile)) {
			unlink($cacheFile);
			return true;
		}
		return false;
	}

	public function flush() {
		if (!$this->cacheDir || $this->cacheDir == '/')
			throw new Exception('Cache directory not configured correctly.');
		foreach (glob($this->cacheDir.'/*') as $cacheFile)
			unlink($cacheFile);
	}

	private function getCacheFile($key) {
		if (!$this->cacheDir || $this->cacheDir == '/')
			throw new Exception('Cache directory not configured correctly.');
		return $this->cacheDir.'/'.md5($key).'.cache';
	}

}
