<?php

// Site details
$config['site.name'] = 'My Photo Gallery';
$config['site.url'] = '';
$config['site.theme'] = 'Theme.Default';

// Album / image sorting.  Valid values:
// SORT_NAME / SORT_NAME_DESCENDING
// SORT_DATE / SORT_DATE_DESCENDING
// SORT_EXIF_DATE / SORT_EXIF_DATE_DESCENDING
$config['gallery.sorting.root'] = SORT_NAME_DESCENDING;
$config['gallery.sorting.albums'] = SORT_NAME;
$config['gallery.sorting.items'] = SORT_EXIF_DATE;

// Image sizes
$config['gallery.thumbnailSize'] = 128;
$config['gallery.imageSize'] = 800;
$config['gallery.downloadFullSize'] = true;

// Administration site
$config['admin.username'] = 'admin';
$config['admin.password'] = 'password';

// Media store
$config['mediastore.provider'] = 'MediaStore.Filesystem';
$config['mediastore.filesystem.directory'] = dirname(dirname(dirname(__FILE__))).'/gallery';

// Cache configuration
$config['cache.provider'] = 'Cache.Filesystem';
$config['cache.filesystem.directory'] = dirname(dirname(dirname(__FILE__))).'/cache';
