<?php

interface pixotic_MediaStore {

	/**
	 * Get a list of root albums in this store.
	 */
	public function getAlbums();

	/**
	 * Get an album from the store by ID.
	 * @param string $albumId The album ID
	 */
	public function getAlbum($albumId);

	/**
	 * Create a new album.
	 * @param string $name The album name
	 * @param pixotic_Album The parent album
	 */
	public function createAlbum($name, $parent = null);

	/**
	 * Rename an album.
	 * @param string $name The album ID
	 * @param string $name The new album name
	 */
	public function renameAlbum($albumId, $name);

	/**
	 * Move an album to a different parent.
	 * @param string $name The album ID
	 * @param string $name The new album name
	 */
	public function moveAlbum($albumId, $parent = null);

	/**
	 * Delete an album.
	 * @param string $id The album id
	 */
	public function deleteAlbum($albumId);

	/**
	 * Get a media item from the store by ID.
	 * @param string $itemId The item ID
	 */
	public function getItem($itemId);

	/**
	 * Add a new media item to the specified album.
	 * @param string $name The name of the item.
	 * @param mixed $file The media file.
	 * @param pixotic_Album $album The album to add the item to.
	 */
	public function createItem($name, $file, $album);

	/**
	 * Rename an media item.
	 * @param string $name The media item ID
	 * @param string $name The new item name
	 */
	public function renameItem($itemId, $name);

	/**
	 * Move a media item to a different album.
	 * @param string $name The item ID
	 * @param string $name The new item name
	 */
	public function moveItem($itemId, $album);

	/**
	 * Delete a media item.
	 * @param string $id The item id
	 */
	public function deleteItem($itemId);

	/**
	 * Check if this media store is writable, or readonly.
	 * @return boolean true if it can be written to.
	 */
	public function isWritable();

}
