<?php

// Site details
$siteName = 'Jeremy and Dalida Jongsma';
$baseUrl = '';
$theme = 'default';

// Album root
$albumDirectory = '/home/jeremy/Pictures';

// Album / image sorting
// Valid values:
// FILENAME
// FILENAME_DESCENDING
// FILE_DATE
// FILE_DATE_DESCENDING
// EXIF_DATE
// EXIF_DATE_DESCENDING
$albumSort = FILENAME_DESCENDING;
$subAlbumSort = EXIF_DATE_DESCENDING;
$imageSort = EXIF_DATE;

// Album grid size
$albumRows = 10;
$albumCols = 5;

// Administration - hiding/showing albums
$adminUsername = 'admin';
$adminPassword = 'password';

// Image cacheing / resizing
$cacheDirectory = dirname(dirname(__FILE__)).'/cache';
$thumbnailSize = 128;
$cacheThumbnails = true;
$imageSize = 640;
$cacheImages = true;

// Allow users to download fullsize originals
$downloadFullSize = true;
