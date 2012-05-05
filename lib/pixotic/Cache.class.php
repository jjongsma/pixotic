<?php

interface pixotic_Cache {

	/**
	 * Get an entry from the cache.  If the entry is not found, or is
	 * older than the $newerThan timestamp, return null.
	 * @return string The cached data
	 */
	public function get($key, $newerThan = null);

	/**
	 * Check if an entry exists in the cache.  If the entry is not found,
	 * or is older than the $newerThan timestamp, return false.
	 * @return boolean true if the entry exists
	 */
	public function exists($key, $newerThan = null);

	/**
	 * Put an entry in the cache.
	 * @param string $key The cache key
	 * @param string $data The data to cache
	 */
	public function put($key, $data);

	/**
	 * Remove an entry from the cache.
	 * @param string $key The cache key
	 */
	public function invalidate($key);

	/**
	 * Clear the entire cache. This will result in a lot of on-demand
	 * data regeneration, resulting in slow requests for awhile - use
	 * with care.
	 */
	public function flush();

}

/**
 * Represents a single entry in the cache.
 */
class pixotic_CacheEntry {

	private $key;
	private $data;
	private $mimeType;
	private $timestamp;

	/** 
	 * Creates a new cache entry.
	 */
	public function __construct($key, $data, $mimeType, $timestamp) {
		$this->key = $key;
		$this->data = $data;
		$this->mimeType = $mimeType;
		$this->timestamp = $timestamp;
	}

	/**
	 * Get data about this cache entry.  Valid fields are "key", "data",
	 * "mimeType", "timestamp".
	 */
	public function __get($field) {
		return $this->$field;
	}

}
