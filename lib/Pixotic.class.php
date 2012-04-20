<?php

class Pixotic {
	
	private $config;
	private $hasSession = true;
	private $theme = 'default';
	private $rootAlbum = null;

	public function __construct() {
		include('config.inc.php');
		$this->config = get_defined_vars();
		$this->allowLogin = session_start() && $this->config['adminUsername'];
		$this->theme = $this->getConfig('theme', 'default');
	}

	public function getConfig($name, $default = null) {
		if (isset($this->config[$name]))
			return $this->config[$name];
		return $default;
	}

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

	public function isLoggedIn() {
		if ($this->allowLogin && isset($_SESSION['loggedIn']))
			return $_SESSION['loggedIn'];
		return false;
	}

	// Page / template display functions

	public function showPage($template, $context = null) {
		$context = $this->makeContext($context);
		$content = $this->fetchTemplate($template, $context);
		$context['content'] = $content;
		echo $this->fetchTemplate('base.tpl', $context);
	}

	public function showBlock($template, $context = null) {
		echo $this->fetchTemplate($template,
			$this->makeContext($context));
	}

	
	private function makeContext($context = null) {

		$defaultContext = array(
			'pixotic' => $this,
		);

		if ($context)
			return array_merge($defaultContext, $context);

		return $defaultContext;

	}

	private function getAlbumNavigation($active = null, $parent = null) {

		$albums = $parent ? $parent->getAlbums() : $this->getRootAlbum()->getAlbums();
		$albumNav = array();

		foreach ($albums as $a) {

			$albumEntry = array(
				'name' => $a->getName(),
				'path' => $a->getRelPath());

			if (substr($active, 0, strlen($a->getRelPath())) == $a->getRelPath()) {
				if ($a->getRelPath() == $active)
					$albumEntry['selected'] = true;
				if (count($a->getAlbums()) > 0)
					$albumEntry['albums'] = $this->getAlbumNavigation($active, $a);
			}

			$albumNav[] = $albumEntry;

		}

		return $albumNav;

	}

	public function getRootAlbum() {
		if (!$this->rootAlbum)
			$this->rootAlbum = new Pixotic_Album($this->getConfig('albumDirectory'), null, $this);
		return $this->rootAlbum;
	}

	public function getBaseURL() {
		return $this->getConfig('baseUrl', '');
	}

	public function getThemeURL($path = null) {
		return $this->getBaseURL().'/themes/'.$this->theme.'/'.$path;
	}

	public function getDefaultAlbum($album = null) {

		if (!$album)
			$album = $this->getRootAlbum();

		$albums = $album->getAlbums();

		foreach ($albums as $a) {
			if (count($a->getImages() > 0))
				return $a;
			if (count($a->getAlbums() > 0)) {
				$d = $this->getDefaultAlbum();
				if ($d)
					return $d;
			}
		}

		return null;

	}

	private function fetchTemplate($template, $context = null) {

		if ($context)
			extract($context);

		ob_start();
		include(dirname(dirname(__FILE__)).'/themes/'.$this->theme.'/'.$template);
		$block = ob_get_contents();
		ob_end_clean();

		return $block;

	}

}

class Pixotic_Album {

	private $path = null;
	private $parent = null;
	private $pixotic = null;

	private $name = null;
	private $images = null;
	private $albums = null;

	private $validExts = array(
		'jpg', 'jpeg', 'gif', 'bmp', 'png', 'svg', 'tif', 'tiff'
	);

	public function __construct($path, $parent, $pixotic) {

		$this->path = $path;
		$this->parent = $parent;
		$this->pixotic = $pixotic;

		$this->name = basename($path);

	}

	public function getAlbums() {
		if ($this->albums === null) {
			$this->albums = array();
			$dh = opendir($this->path);
			while ($d = readdir($dh)) {
				if ($d{0} == '.')
					continue;
				if (is_dir($this->path.'/'.$d))
					$this->albums[] = new Pixotic_Album($this->path.'/'.$d,
						$this, $this->pixotic);
			}
		}
		return $this->albums;
	}

	public function getImages() {
		if ($this->images === null) {
			$this->images = array();
			$dh = opendir($this->path);
			while ($f = readdir($dh)) {
				if ($f{0} == '.')
					continue;
				if (is_dir($this->path.'/'.$f))
					$this->images[] = new Pixotic_Image($this->path.'/'.$f,
						$this, $this->pixotic);
			}
		}
		return $this->images;
	}

	public function getName() {
		return $this->name;
	}

	public function getParent() {
		return $this->parent;
	}

	public function getPath() {
		return $this->path;
	}

	public function getRelPath() {
		return substr($this->path, strlen($this->pixotic->getConfig('albumDirectory')) + 1);
	}

}

class Pixotic_Image {

	private $path = null;
	private $album = null;
	private $pixotic = null;

	private $name;
	private $thumbnail = null;
	private $resized = null;
	private $fullsize = null;

	public function __construct($path, $album, $pixotic) {

		$this->path = $path;
		$this->album = $album;
		$this->pixotic = $pixotic;

		$this->name = basename($path);

	}

	public function getName() {
		return $this->name;
	}

	public function getAlbum() {
		return $this->album;
	}

	public function getPath() {
		return $this->path;
	}

	public function getRelPath() {
		return substr($this->path, strlen($this->pixotic->getConfig('albumDirectory')) + 1);
	}

	public function getThumbnail() {
		if (!$this->thumbnail) {
			$this->thumbnail = new Pixotic_ResizedImage(
				$this->path, $this->album, $this->pixotic,
				$pixotic->getConfig('thumbnailSize', 128));
		}
		return $this->thumbnail;
	}

	public function getResized() {
		if (!$this->resized) {
			$this->resized = new Pixotic_ResizedImage($this->path,
				$pixotic->getConfig('imageSize', 800));
		}
		return $this->thumbnail;
	}

	public function getFullSize() {
		if (!$this->fullsize)
			$this->fullsize = new Pixotic_ResizedImage($this->path);
		return $this->fullsize;
	}

}

class Pixotic_ResizedImage {

	private $path = null;
	private $size = null;
	private $cache = null;

	public function __construct($path, $size, $cache) {
		$this->path = $path;
		$this->size = $size;
		$this->cache = $cache;
	}

	public function getBytes() {
	}

	public function isCached() {
		return false;
	}

}

