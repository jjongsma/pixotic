<?php

/**
 * Represents an album.
 */
interface pixotic_Album {

	/**
	 * Gets this album's unique ID for use in URLs, etc.
	 */
	public function getID();

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
	 * Get the name of this album.
	 */
	public function getName();

	/**
	 * Get this album's parent album.
	 */
	public function getParent();

	/**
	 * Set this album as private.
	 */
	public function setPrivate($private = true);

}

