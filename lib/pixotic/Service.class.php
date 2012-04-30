<?php

class pixotic_Service {

	public static $CACHE = 'CACHE';
	public static $ADMIN_MODULE = 'ADMIN_MODULE';
	public static $ALBUM_VIEW = 'ALBUM_VIEW';
	public static $MEDIA_FRAME = 'MEDIA_FRAME';
	public static $MEDIA_STORE = 'MEDIA_STORE';
	public static $MEDIA_TOOLKIT = 'MEDIA_TOOLKIT';
	public static $MEDIA_VIEW = 'MEDIA_VIEW';
	public static $SLIDESHOW = 'SLIDESHOW';
	public static $TEMPLATE = 'TEMPLATE';

	private $module;

	public function __construct($module) {
		$this->module = $module;
	}

	public function getModule() {
		return $this->module;
	}

}
