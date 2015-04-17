<?php
/*
 * CMS module: Download Gallery 2
 * Copyright (C) 2004-2006, Ryan Djurovich
 * Improvements are copyright (c) 2009-2011 Frank Heyne
 * For more information see info.php
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));

//Remove all table entries and drop some tables.
$database->query("DELETE FROM ".TABLE_PREFIX."search WHERE name = 'module' AND value = 'download_gallery'");
$database->query("DELETE FROM ".TABLE_PREFIX."search WHERE extra = 'download_gallery'");
$database->query("DROP TABLE ".TABLE_PREFIX."mod_download_gallery_files");
$database->query("DROP TABLE ".TABLE_PREFIX."mod_download_gallery_settings");
$database->query("DROP TABLE ".TABLE_PREFIX."mod_download_gallery_groups");
$database->query("DROP TABLE ".TABLE_PREFIX."mod_download_gallery_file_ext");

//Remove the download_gallery folder in the media dir
require_once(WB_PATH.'/framework/functions.php');
rm_full_dir(WB_PATH . MEDIA_DIRECTORY . '/download_gallery');

?>