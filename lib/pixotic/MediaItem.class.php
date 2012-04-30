<?php

/** 
 * Represents a media item in Pixotic.
 */
interface pixotic_MediaItem {

	/**
	 * Get the unique ID for this item, for use in URLs, etc.
	 */
	public function getID();

	/**
	 * Get the absolute path of this item's file.
	 */
	public function getAbsolutePath();

	/**
	 * Get the name of this item.
	 */
	public function getName();

	/**
	 * Get the album this item belongs to.
	 */
	public function getAlbum();

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

}

