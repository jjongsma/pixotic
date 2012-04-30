<?php

interface pixotic_MediaFrame {
	
	/**
	 * Wrap the specified HTML code in a frame.
	 */
	public abstract function decorate($html)

	/**
	 * Get a list of stylesheets to include in the page to properly render.
	 * This should return relative paths from the app root (see
	 * Pixotic::getModuleDirectory($module)).
	 */
	public abstract function getStylesheets();

	/**
	 * Get a list of scripts to include in the page to properly render.
	 * This should return relative paths from the app root (see
	 * Pixotic::getModuleDirectory($module)).
	 */
	public abstract function getScripts();

}
