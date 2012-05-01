<?php

/**
 * Represents an album.
 */
interface pixotic_ItemBase {

	/**
	 * Gets this item's unique ID for use in URLs, etc.
	 */
	public function getID();

	/**
	 * Get the name of this album.
	 */
	public function getName();

	/**
	 * Get this album's parent album.
	 */
	public function getAlbum();

	/**
	 * Get the last time this item was modified.
	 */
	public function getLastModified();

}

