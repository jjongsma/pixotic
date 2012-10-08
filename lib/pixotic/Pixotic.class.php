<?php
define ('PIXOTIC', dirname(dirname(dirname(__FILE__))));
require_once('Service.class.php');
require_once('TemplateEngine.class.php');

class Pixotic {
	
	private $config;
	private $allowLogin = true;
	private $baseUrl;

	private $sorter;
	private $modules;
	private $services;
	private $templateEngine;
	private $mediaStore;
	private $log;

	private $metadata = array();

	public function __construct() {

		$config = array();
		include(PIXOTIC.'/lib/pixotic/config.defaults.inc.php');
		include(PIXOTIC.'/lib/config.inc.php');

		$this->config = $config;

		$this->log = new pixotic_Log($config['site.debug'] ? 'debug' : 'info');
		$this->log->debug('Request started');
		$this->log->trace('start Pixotic::__construct()');

		$this->allowLogin = session_start() && $this->config['admin.username'];
		$this->baseUrl = $this->getConfig('site.url', '');

		$this->templateEngine = new pixotic_TemplateEngine(
			$this->getConfig('site.theme', 'Theme.Default'), $this);

		$this->initializeModules();

		$this->mediaStore = $this->getService(pixotic_Service::$MEDIA_STORE,
			$this->getConfig('mediastore.provider', 'MediaStore.Filesystem'));

		$this->log->trace('end Pixotic::__construct()');

	}

	private function initializeModules() {

		$this->modules = array();

		$this->services = array(
			pixotic_Service::$ADMIN_MODULE => array(),
			pixotic_Service::$ALBUM_VIEW => array(),
			pixotic_Service::$MEDIA_FRAME => array(),
			pixotic_Service::$MEDIA_STORE => array(),
			pixotic_Service::$MEDIA_TOOLKIT => array(),
			pixotic_Service::$MEDIA_VIEW => array(),
			pixotic_Service::$SLIDESHOW => array(),
			pixotic_Service::$TEMPLATE => array()
		);

		$moduleDir = PIXOTIC.'/modules';

		foreach (scandir($moduleDir) as $md) {
			if ($md{0} != '.' && is_dir($moduleDir.'/'.$md)) {
				$moduleFile = $moduleDir.'/'.$md.'/'.$md.'.class.php';
				if (file_exists($moduleFile)) {
					require_once($moduleFile);
					$moduleClass = 'pixotic_'.str_replace('.', '_', $md);
					if (class_exists($moduleClass)) {
						$module = new $moduleClass();
						if ($module instanceof pixotic_Module) {
							$this->modules[$md] = $module;
							$module->activate($this);
						} else {
							throw new Exception("Module class must extend pixotic_Module");
						}
					}
				}
			}
		}

	}

	public function getModule($id) {
		if (isset($this->modules[$id]))
			return $this->modules[$id];
		return null;
	}

	public function registerService($service, $provider) {
		$this->services[$service][] = $provider;
	}

	public function unregisterService($service, $provider) {
		foreach ($this->services[$service] as $i => $s)
			if ($s === $service)
				unset($this->services[$service][$i]);
	}

	public function getMediaToolkits($mimeType) {
		$matches = array();
		foreach ($this->services[pixotic_Service::$MEDIA_TOOLKIT] as $tk) {
			if (in_array($mimeType, $tk->getSupportedMimeTypes()))
				$matches[] = $tk;
		}
		return $matches;
	}

	public function getMediaToolkit($mimeType, $action) {

		$toolkits = $this->getMediaToolkits($mimeType, $action);

		foreach ($toolkits as $tk)
			if (in_array($action, $tk->getSupportedActions()))
				return $tk;

		return null;

	}

	public function getService($type, $id = null) {
		foreach ($this->services[$type] as $s) {
			if (!$id || (isset($this->modules[$id]) && $s == $this->modules[$id]))
				return $s;
		}
		return null;
	}

	// Configuration management

	public function getConfig($name, $default = null) {
		if (isset($this->config[$name]))
			return $this->config[$name];
		return $default;
	}

	public function getRealPath($path = null) {
		return dirname(dirname(dirname(__FILE__))).'/'.$path;
	}

	public function getRealURL($path = null) {
		return $this->baseUrl.$path;
	}

	public function getThemePath($resource) {
		return $this->getRealPath('modules/'.$this->getConfig('site.theme').'/'.$resource);
	}

	// Login management

	public function login($username, $password) {
		if ($this->allowLogin
				&& $this->config['admin.username'] == $username
				&& $this->config['admin.password'] == $password) {
			$_SESSION['loggedIn'] = true;
			return true;
		}
		$_SESSION['loggedIn'] = false;
		return false;
	}

	public function logout() {
		$_SESSION['loggedIn'] = false;
	}

	public function isLoggedIn() {
		if ($this->allowLogin && isset($_SESSION['loggedIn']))
			return $_SESSION['loggedIn'];
		return false;
	}

	public function isAdmin() {
		// No separate permissions for now
		return $this->isLoggedIn();
	}

	// Page / template display

	public function showPage($template, $context = null) {
		$this->templateEngine->showPage($template, $context);
	}

	// Root albums

	public function getAlbums() {
		return $this->mediaStore->getAlbums();
	}

	public function getDefaultAlbum() {
		return $this->mediaStore->getDefaultAlbum();
	}

	public function getAlbum($id) {
		return $this->mediaStore->getAlbum($id);
	}

	public function getItem($id) {
		return $this->mediaStore->getItem($id);
	}

	public function getAlbumThumbnail($album) {

		$this->log->debug('in getAlbumThumbnail()');

		$size = $this->getConfig('gallery.thumbnailSize');
		$cacheFile = $this->getConfig('gallery.imageCache')
			.'/'.md5('album-'.$album->getID().'-'.$size).'.png';

		if (file_exists($cacheFile) && filemtime($cacheFile) >= filemtime($original)) {
			$this->log->debug('getAlbumThumbnail() unmodified');
			return $cacheFile;
		}

		$toolkit = $this->getMediaToolkit('image/png',
			pixotic_MediaToolkit::$ACTION_ALBUM_THUMBNAIL);

		if ($toolkit != null) {
			$this->log->debug('Regenerating album thumbnail');
			$toolkit->createAlbumThumbnail($album, $size, 'image/png', $cacheFile);
			$this->log->debug('getAlbumThumbnail() finished');
			return $cacheFile;
		}

		return null;

	}

	public function getItemThumbnail($item) {
		return $this->getItemPreview($item, $this->getConfig('gallery.thumbnailSize'));
	}

	public function getItemPreview($item, $size) {

		$this->log->debug('in getItemPreview()');

		$original = $item->getAbsolutePath();
		$ext = array_pop(explode('.', $original));
		$cacheFile = $this->getConfig('gallery.imageCache').'/'.md5($original.'-'.$size).'.'.$ext;

		if (file_exists($cacheFile) && filemtime($cacheFile) >= filemtime($original)) {
			$this->log->debug('getItemPreview() unmodified');
			return $cacheFile;
		}

		$toolkit = $this->getMediaToolkit($item->getMimeType(), pixotic_MediaToolkit::$ACTION_RESIZE);

		if ($toolkit != null) {
			$this->log->debug('Regenerating image cache');
			$toolkit->scaleItem($item, $size, $cacheFile);
			$this->log->debug('getItemPreview() finished');
			return $cacheFile;
		}

		return null;

	}

	public function getMetadata($item) {

		$id = $item->getID();

		if (!$this->metadata[$id]) {

			$toolkit = $this->getMediaToolkit($item->getMimeType(),
				pixotic_MediaToolkit::$ACTION_READ_METADATA);

			if ($toolkit != null)
				$this->metadata[$id] = $toolkit->getMetadata($item);

		}

		return $this->metadata[$id];

	}

	public function sendFile($file, $download = false) {

		$this->log->debug('in sendFile()');

		$overrides = array(
			'.css' => 'text/css',
			'.html' => 'text/html',
			'.js' => 'text/javascript',
			'.css' => 'text/css',
			'.png' => 'image/png',
			'.jpg' => 'image/jpeg',
			'.jpeg' => 'image/jpeg',
			'.gif' => 'image/gif'
		);

		if (file_exists($file) && is_file($file)) {
		
			if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER)) {
				$lastmod = filemtime($file);
				$lastreq = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
				if (array_key_exists('HTTP_CACHE_CONTROL', $_SERVER)) {
					$cc = $_SERVER['HTTP_CACHE_CONTROL'];
					// max-age just specifies to validate against modified time
					//if ($cc == 'max-age=0' || $cc = 'no-cache')
					if ($cc == 'no-cache')
						$lastreq = 0;
				}
				if ($lastmod <= $lastreq) {
					header('HTTP/1.1 304 Not Modified');
					header('Pragma: phpsucks');
					header('Cache-Control: public');
					$this->log->debug('sendFile() returned cache');
					exit;
				}
			}

			$ext = substr($file, strrpos($file, '.'));

			if (array_key_exists($ext, $overrides))
				$mimetype = $overrides[$ext];
			else
				//$mimetype = mime_content_type($file);
				$mimetype = 'text/plain';
			header('Pragma: phpsucks');
			header('Cache-Control: public');
			header('Content-type: '.$mimetype);
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
			if ($download) {
				header('Content-Disposition: attachment; filename='.basename($file));
			}

			if ($fh = fopen($file, 'r')) {
				while (!feof($fh))
					echo fread($fh, 8192);
				fclose($fh);
			}
			$this->log->debug('sendFile() finished');

		} else {

			header('Status: 404 Not Found');
			echo 'Unable to cache image.  Please check your configuration.';

		}

	}

	public function getLog() {
		return $this->log;
	}

}

class pixotic_Log {

	private $startTime;
	private $lastTime;
	private $level;

	private $levels = array(
		'trace' => 0,
		'debug' => 1,
		'info' => 2,
		'warn' => 3,
		'error' => 4,
		'fatal' => 5);

	private $entries = array();

	public function __construct($level) {
		$this->level = $this->levels[$level];
		$this->startTime = microtime(true);
		$this->lastTime = $this->startTime;
	}

	public function trace($message) {
		$this->log($message, 'trace');
	}

	public function debug($message) {
		$this->log($message, 'debug');
	}

	public function info($message) {
		$this->log($message, 'info');
	}

	public function warn($message) {
		$this->log($message, 'warn');
	}

	public function error($message) {
		$this->log($message, 'error');
	}

	public function fatal($message) {
		$this->log($message, 'fatal');
	}

	private function log($message, $level) {

		if ($this->levels[$level] < $this->level)
			return;

		$now = microtime(true);
		$millis = $now - floor($now);
		$elapsed = round(($now - $this->lastTime) * 1000, 2);
		$this->entries[] = array($level, strftime('%H:%m:%S.').sprintf('%03d', ($millis * 1000)).' (+'.$elapsed.'ms)', $message);
		$this->lastTime = $now;

	}

	public function getEntries() {
		return $this->entries;
	}

}
