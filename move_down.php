<?php
/*
 * Copyright and more information see file info.php
 */

require('../../config.php');

// Get id
$id = '';
if(!isset($_GET['file_id']) OR !is_numeric($_GET['file_id'])) {
	if(!isset($_GET['group_id']) OR !is_numeric($_GET['group_id'])) {
		header("Location: index.php");
	} else {
		$id = (int) $_GET['group_id'];
		$id_field = 'group_id';
		$table = TABLE_PREFIX.'mod_download_gallery_groups';
	}
} else {
	$id = (int) $_GET['file_id'];
	$id_field = 'file_id';
	$table = TABLE_PREFIX.'mod_download_gallery_files';
}

require(WB_PATH.'/modules/admin.php');				// Include WB admin wrapper script
require(WB_PATH.'/framework/class.order.php');			// Include the ordering class

// Create new order object an reorder
$order = new order($table, 'position', $id_field, 'section_id');

if($order->move_down($id)) {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
} else {
	$admin->print_error($TEXT['ERROR'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

?>