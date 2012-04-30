<?php

interface pixotic_Template {

	/**
	 * Display the page template around the specified page content.
	 * @param string $content The page content
	 */
	public function display($content);

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
