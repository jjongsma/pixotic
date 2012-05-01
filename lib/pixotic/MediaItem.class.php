<?php
require_once('ItemBase.class.php');

/** 
 * Represents a media item in Pixotic.
 */
interface pixotic_MediaItem extends pixotic_ItemBase {

	/**
	 * Get the absolute path of this item's file.
	 */
	public function getAbsolutePath();

	/**
	 * Get the original width of this media item.
	 */
	public function getWidth();

	/**
	 * Get the original height of this media item.
	 */
	public function getHeight();

	/**
	 * Get the description of this media item.
	 */
	public function getDescription();

	/**
	 * Get the MIME type of this media item.
	 */
	public function getMimeType();

}

