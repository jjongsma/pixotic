<?php

interface pixotic_AlbumView {

	/**
	 * Render the page body of the album.  This should not return contents with
	 * page dressing on it as it will be used for selective AJAX content swapping
	 * as well.
	 *
	 * @param pixotic_Album $album The album to display
	 */
	public function display($album);

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
