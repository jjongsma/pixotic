<?php

interface pixotic_Cache {

	/**
	 * Get an entry from the cache.  If the entry is not found, or is
	 * older than the $expiration time in seconds, return null.
	 *
	 * @return string The cached data
	 */
	public function get($key, $expiration = null);

	/**
	 * Put an entry in the cache.
	 * @param string $key The cache key
	 * @param string $data The data to cache
	 */
	public function put($key, $data);

}

/**
 * Represents a single entry in the cache.
 */
class pixotic_CacheEntry {

	private $key;
	private $data;
	private $timestamp;

	/** 
	 * Creates a new cache entry.
	 */
	public function __construct($key, $data, $timestamp) {
		$this->key = $key;
		$this->data = $data;
		$this->timestamp = $timestamp;
	}

	/**
	 * Get data about this cache entry.  Valid fields are "key", "data",
	 * "timestamp".
	 */
	public function __get($field) {
		return $this->$field;
	}

}
