<?php
require_once('ItemBase.class.php');

/**
 * Represents an album.
 */
interface pixotic_Album extends pixotic_ItemBase {

	/**
	 * Gets an array of sub-albums in this album.
	 * @param boolean $private If true, include albums marked private
	 */
	public function getAlbums($private = false);

	/**
	 * Get an array of media items in this album.
	 */
	public function getItems();

	/**
	 * Set this album as private.
	 */
	public function setPrivate($private = true);

}

