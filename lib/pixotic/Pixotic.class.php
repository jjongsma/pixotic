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

	private $metadata = array();

	public function __construct() {

		$config = array();
		include(PIXOTIC.'/lib/config.inc.php');

		$this->config = array_merge(get_defined_vars(), $config);
		$this->allowLogin = session_start() && $this->config['adminUsername'];
		$this->baseUrl = $this->getConfig('baseUrl', '');

		$this->templateEngine = new pixotic_TemplateEngine(
			$this->getConfig('theme', 'Theme.Default'), $this);

		$this->initializeModules();

		$this->mediaStore = $this->getService(pixotic_Service::$MEDIA_STORE,
			$this->getConfig('mediaStore', 'MediaStore.Filesystem'));

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

	public function getMediaToolkit($mimeType) {
		$matches = array();
		foreach ($this->services[pixotic_Service::$MEDIA_TOOLKIT] as $tk) {
			if (in_array($mimeType, $tk->getSupportedMimeTypes()))
				$matches[] = $tk;
		}
		return $matches;
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

	// Login management

	public function login($username, $password) {
		if ($this->allowLogin
				&& $this->config['adminUsername'] == $username
				&& $this->config['adminPassword'] == $password) {
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

	public function getMetadata($item) {

		$id = $item->getID();

		if (!$this->metadata[$id]) {

			$toolkits = $this->getMediaToolkit($item->getMimeType());
			$toolkit = null;

			foreach ($toolkits as $tk) {
				if (in_array(pixotic_MediaToolkit::$ACTION_READ_METADATA,
						$tk->getSupportedActions())) {
					$toolkit = $tk;
					break;
				}
			}

			if ($toolkit != null)
				$this->metadata[$id] = $tk->getMetadata($item);

		}

		return $this->metadata[$id];

	}

}
