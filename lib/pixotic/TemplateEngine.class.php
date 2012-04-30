<?php

class pixotic_TemplateEngine {

	private $pixotic;
	private $theme = 'theme.default';
	private $deviceType = 'desktop';

	private $defaultContext;

	public function __construct($theme, $pixotic) {

		$this->theme = $theme;
		$this->pixotic = $pixotic;
		$this->deviceType = $this->detectDeviceType();

		$this->defaultContext = array(
			'pixotic' => $this->pixotic
		);

	}

	private function detectDeviceType() {
		return 'desktop';
	}

	private function getThemeRelativeURL($path = null) {

		$themeUrl = '/modules/'.$this->theme.'/'.$path;
		$deviceUrl = '/modules/'.$this->theme.'/'.$this->deviceType.'/'.$path;

		return file_exists($this->pixotic->getRealPath($deviceUrl))
				? $deviceUrl
				: $themeUrl;

	}

	private function getThemeURL($path = null) {
		return $this->pixotic->getRealURL($this->getThemeRelativeURL($path));
	}

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

	private function fetchTemplate($template, $context = null) {

		if ($context)
			extract($context);

		$realTemplate = $this->pixotic->getRealPath($this->getThemeRelativeURL($template));

		if (!file_exists($realTemplate))
			return $this->fetchTemplate('notfound.tpl', array(
				'title' => 'Template Not Found',
				'error' => 'The template <b>'.$template.'</b> was not found.'));

		ob_start();
		include($realTemplate);
		$block = ob_get_contents();
		ob_end_clean();

		return $block;

	}
	
	private function makeContext($context = null) {

		if (!$context)
			$context = array();

		return array_merge($this->defaultContext, $context);

	}

	public function getAlbumNavigation($active = null, $parent = null) {

		$albums = $parent ? $parent->getAlbums() : $this->pixotic->getAlbums();
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

}
