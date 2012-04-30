<?php

interface pixotic_Slideshow {

	/**
	 * Start a slideshow for the specified album or media items.  This should not
	 * return contents with page dressing on it as it will be used for selective
	 * AJAX content swapping as well.
	 * @param mixed $albumOrItems The album or item list to display
	 */
	public function display($albumOrItems);

	/**
	 * Get a list of stylesheets to include in the page to properly render.
	 * This should return relative paths from the app root (see
	 * Pixotic::getModuleDirectory($module)).
	 */
	public function getStylesheets();

	/**
	 * Get a list of scripts to include in the page to properly render.
	 * This should return relative paths from the app root (see
	 * Pixotic::getModuleDirectory($module)).
	 */
	public function getScripts();

}
