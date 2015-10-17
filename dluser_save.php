<?php
/*
 * CMS module: Download Gallery 2
 * Copyright (C) 2004-2006, Ryan Djurovich
 * Improvements are copyright (c) 2009-2011 Frank Heyne
 * For more information see info.php
*/

// Include config file
require('../../config.php');
//require(WB_PATH.'/modules/admin.php');				// Include WB admin wrapper script
require_once(WB_PATH.'/framework/class.order.php');		// Include the ordering class

include_once(WB_PATH.'/modules/download_gallery/functions.php');

// Include admin class
require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Start', 'start', false, false);

if (isset($_GET['sid'])) {
	$section_id = (int) $_GET['sid'];
}	
if (isset($_GET['pid'])) {
	$page_id = (int) $_GET['pid'];
}

// Retrieve settings
$query_settings = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_settings WHERE section_id = '".$section_id."'");
$settings = $query_settings->fetchRow();
$use_captcha = $settings['use_captcha']; 

require_once(WB_PATH.'/framework/functions.php');
$userupload = $settings['userupload'];
if($userupload=="0" or $section_id==0 or $page_id==0){
	header('Location: '.WB_URL.'/pages/');
	exit;
}

// Captcha
if(extension_loaded('gd') AND function_exists('imageCreateFromJpeg')) { /* Make's sure GD library is installed */
	if($use_captcha) {
		if(isset($_POST['captcha']) AND $_POST['captcha'] != ''){
			// Check for a mismatch
			if(!isset($_POST['captcha']) OR !isset($_SESSION['captcha']) OR $_POST['captcha'] != $_SESSION['captcha']) {
				$captcha_error = $MESSAGE['MOD_FORM']['INCORRECT_CAPTCHA'];
			}
		} else {
			$captcha_error = $MESSAGE['MOD_FORM']['INCORRECT_CAPTCHA'];
		}
	}
}
if(isset($_SESSION['captcha'])) {
	unset($_SESSION['captcha']);
}
if(isset($captcha_error)) {
	echo '<p><strong>'.$captcha_error.'<strong></p>';
	echo '<p><a href="javascript: history.go(-1);">'.$TEXT['BACK'].'</a></p>';
	exit;
} else {
	// Validate all fields
	if($admin->get_post('title') == '' AND $admin->get_post('url') == '') {
		echo $MESSAGE['GENERIC']['FILL_IN_ALL']." -<a href=\"javascript: history.go(-1);\">BACK</a>-";
		exit;
	} else {
		$title = $admin->add_slashes(htmlspecialchars($admin->get_post('title')));
		$description = $admin->add_slashes(htmlspecialchars($admin->get_post('description')));
		$fgroup = (int) $admin->get_post('fgroup');
	}

	$file_id = '';

	// Get new order
	$order = new order(TABLE_PREFIX.'mod_download_gallery_files', 'position', 'file_id', 'section_id');
	$position = $order->get_new($section_id);

	$update_when_modified = true; 		// Tells script to update when this page was last updated

	// Get page link URL
	$query_page = $database->query("SELECT level,link FROM ".TABLE_PREFIX."pages WHERE page_id = '$page_id'");
	$page = $query_page->fetchRow();
	$page_level = $page['level'];
	$page_link = $page['link'];

	// Make sure the download_gallery directory is set and exists
	make_dl_dir();

	// Check if the user uploaded an file
	if(isset($_FILES['file']['tmp_name']) AND $_FILES['file']['tmp_name'] != '') {
		// Get real filename and set new filename
		$filename = trim(str_replace(chr(0), '', $_FILES['file']['name']));
		$path_parts = pathinfo($filename);
		$fileext = trim(strtolower($path_parts['extension']));
		
		//Retrieve extension lists from database
		$query_fileext 	= $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_file_ext WHERE section_id = '$section_id'");
		$found = false;
		if($query_fileext->numRows() > 0) {
			while($exts = $query_fileext->fetchRow()) {
				if (in_array($fileext, explode(",", $exts['extensions']))) {
					$found = true;
					break;
				}
			}
		}
		if (($fileext == '') or !$found)
		exit($DGTEXT['Wrong file extension']);
		
		// Insert new row into database
		$database->query("INSERT INTO ".TABLE_PREFIX."mod_download_gallery_files (section_id, page_id, position, active)
						 VALUES ('$section_id', '$page_id', '$position', '1')");
	
		// Get the id
		$file_id = $database->get_one("SELECT LAST_INSERT_ID()");

		// we only want downloads via the gallery, so add some random part to the file name
		$new_fn = $path_parts['filename'].'_'.rand(10000, 99999).'.'.$fileext;
		$new_filename = WB_PATH.MEDIA_DIRECTORY.'/download_gallery/'.$new_fn;
		// Work-out what the link should be
		$file_link = WB_URL.MEDIA_DIRECTORY.'/download_gallery/'.$new_fn;
		
		if (!file_exists($new_filename)) {
			// Upload file
			move_uploaded_file($_FILES['file']['tmp_name'], $new_filename);
			change_mode($new_filename);
		}
		// update file information in the database
		$database->query("UPDATE ".TABLE_PREFIX."mod_download_gallery_files SET extension = '$fileext', filename = '$new_filename' WHERE file_id = '$file_id'");
	} else exit($DGTEXT['Missing file']);

	if ($fgroup > 0) {
		$thegroup = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_groups WHERE group_id = '$fgroup'");
		if ($thegroup->numRows() == 0) {  
			$fgroup = 0;  // user specified group does not exist
		}
	}

	$updatequery = "UPDATE ".TABLE_PREFIX."mod_download_gallery_files SET title = '$title', link = '$file_link',
		`description` = '$description', active = '1', `group_id` = '$fgroup', 
		modified_when = '".time()."', modified_by = '".$admin->get_user_id()."' WHERE file_id = '$file_id'";
	// Update row
	$database->query($updatequery);
	// Get page link
    $query_page = $database->query("SELECT link FROM ".TABLE_PREFIX."pages WHERE page_id = '$page_id'");
    $page = $query_page->fetchRow();
    header('Location: '.page_link($page['link']));
}
?>