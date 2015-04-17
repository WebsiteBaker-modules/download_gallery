<?php
/*
 * CMS module: Download Gallery 2
 * Copyright (C) 2004-2006, Ryan Djurovich
 * Improvements are copyright (c) 2009-2011 Frank Heyne
 * For more information see info.php
*/

require('../../config.php');

// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');

// STEP 0:	initialize some variables
$page_id = (int) $page_id;
$section_id = (int) $section_id;

// Include the ordering class
require(WB_PATH.'/framework/class.order.php');
// Get new order
$order = new order(TABLE_PREFIX.'mod_download_gallery_groups', 'position', 'group_id', 'section_id');
$position = $order->get_new($section_id);

// Insert new row into database
$database->query("INSERT INTO ".TABLE_PREFIX."mod_download_gallery_groups (section_id,page_id,position,active) VALUES ('$section_id','$page_id','$position','1')");

// Get the id
$group_id = $database->get_one("SELECT LAST_INSERT_ID()");

// Say that a new record has been added, then redirect to modify page
if($database->is_error()) {
	$admin->print_error($database->get_error(), WB_URL.'/modules/download_gallery/modify_group.php?page_id='.$page_id.'&section_id='.$section_id.'&group_id='.$group_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], WB_URL.'/modules/download_gallery/modify_group.php?page_id='.$page_id.'&section_id='.$section_id.'&group_id='.$group_id);
}

// Print admin footer
$admin->print_footer();

?>