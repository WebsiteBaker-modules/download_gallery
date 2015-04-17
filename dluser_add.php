<?php
/*
 * CMS module: Download Gallery 2
 * Copyright (C) 2004-2006, Ryan Djurovich
 * Improvements are copyright (c) 2009-2011 Frank Heyne
 * For more information see info.php
*/

// Include config file
require('../../config.php');

// Validation:		Check if details are correct. If not navigate to main.
if(!isset($_GET['sid']) OR !is_numeric($_GET['sid'])) {
	header("Location: ".WB_URL."/pages/");
} else {
	$section_id = (int) $_GET['sid'];
	$page_id = (int) $_GET['pid'];
	define('SECTION_ID', $section_id);
}

require_once(WB_PATH.'/framework/class.database.php');

$query_page = $database->query("SELECT parent,page_title,menu_title,keywords,description,visibility FROM ".TABLE_PREFIX."pages WHERE page_id = '$page_id'");
if($query_page->numRows() == 0) {
	header('Location: '.WB_URL.'/pages/');
} else {
	$page = $query_page->fetchRow();
	// Required page details
	define('PAGE_CONTENT', WB_PATH.'/modules/download_gallery/dluser_page.php');
	// Include index (wrapper) file
	require(WB_PATH.'/index.php');
}

?>