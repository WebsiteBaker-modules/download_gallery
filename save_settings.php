<?php
/* 
 * Copyright and more information see file info.php
 */

require('../../config.php');

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');
include_once(WB_PATH.'/modules/download_gallery/functions.php');

// This code removes any php tags and adds slashes
$friendly = array('&lt;', '&gt;', '?php');
$raw = array('<', '>', '');

// STEP 1: Retrieve settings from POST vars
if (isset($_POST['file_size_decimals']) AND is_numeric($_POST['file_size_decimals'])) {
    $file_size_decimals = $_POST['file_size_decimals'];
} else {
    $file_size_decimals = '0';
}
if (isset($_POST['files_per_page']) AND is_numeric($_POST['files_per_page'])) {
    $files_per_page = $_POST['files_per_page'];
} else {
    $files_per_page = '0';
}

if (isset($_POST['file_size_round']) AND is_numeric($_POST['file_size_round'])) {
    $file_size_roundup = $_POST['file_size_round'];
} else {
    $file_size_roundup = '0';

}if (isset($_POST['search_filter']) AND is_numeric($_POST['search_filter'])) {
    $search_filter = $_POST['search_filter'];
} else {
    $search_filter = '0';
}

if (isset($_POST['ordering']) AND is_numeric($_POST['ordering'])) {
    $ordering = $_POST['ordering'];
} else {
    $ordering = 0;
}

if (isset($_POST['orderby']) AND is_numeric($_POST['orderby'])) {
    $orderby = $_POST['orderby'];
} else {
    $orderby = 0;
}

if (isset($_POST['extordering']) AND is_numeric($_POST['extordering'])) {
    $extordering = $_POST['extordering'];
} else {
    $extordering = 0;
}

if (isset($_POST['pushmode']) AND ($_POST['pushmode'] == 1)) {
    $pushmode = 1;
} else {
    $pushmode = 0;
}

if (isset($_POST['userupload']) AND is_numeric($_POST['userupload'])) {
    $userupload = $_POST['userupload'];
} else {
    $userupload = 0;
}

if (isset($_POST['use_captcha']) AND is_numeric($_POST['use_captcha'])) {
    $use_captcha = $_POST['use_captcha'];
} else {
    $use_captcha = 0;
}

if (isset($_POST['save'])) {
    $header = $admin->add_slashes(str_replace($friendly, $raw, $_POST['header']));
    $footer = $admin->add_slashes(str_replace($friendly, $raw, $_POST['footer']));
    $files_loop = $admin->add_slashes(str_replace($friendly, $raw, $_POST['files_loop']));
    $file_header = $admin->add_slashes(str_replace($friendly, $raw, $_POST['file_header']));
    $file_footer = $admin->add_slashes(str_replace($friendly, $raw, $_POST['file_footer']));
    $gheader = $admin->add_slashes(str_replace($friendly, $raw, $_POST['gheader']));
    $gloop   = $admin->add_slashes(str_replace($friendly, $raw, $_POST['gloop']));
    $gfooter = $admin->add_slashes(str_replace($friendly, $raw, $_POST['gfooter']));
    $search_layout = $admin->add_slashes(str_replace($friendly, $raw, $_POST['search_layout']));
} elseif (isset($_POST['reset_table'])){
    $header = '';
    $footer = '';
    $file_header = '';
    $files_loop = '';
    $file_footer = '';
    $gloop = '';
    $search_layout = '';
    $gheader = '';
    $gfooter = '';
    init_fields($header, $footer, $file_header, $files_loop, $file_footer, $gloop, $search_layout, $gheader, $gfooter);
}

// Update settings
/*['ordering']
0 - ascending position
1 - descending position
2 - ascending title
3 - descending title
orderby:
position=0
title=1
none=9

['extordering']
0 - extension ascending 
1 - extension descending
9 - extension no order
*/

if($ordering==0 and $orderby==0){$ordering=0;}
if($ordering==1 and $orderby==0){$ordering=1;}
if($ordering==0 and $orderby==1){$ordering=2;}
if($ordering==1 and $orderby==1){$ordering=3;}

$query="UPDATE ".TABLE_PREFIX."mod_download_gallery_settings SET
	header = '$header',
	files_loop = '$files_loop',
	footer = '$footer',
	files_per_page = '$files_per_page',
	file_size_roundup = '$file_size_roundup',
	file_size_decimals = '$file_size_decimals',
	file_header = '$file_header',
	file_footer = '$file_footer', 
	ordering = '$ordering', 
	extordering = '$extordering',
	userupload = '$userupload',
    use_captcha = '$use_captcha',
	gheader = '$gheader',
	gloop = '$gloop', 						   
	gfooter = '$gfooter',	
	search_filter = '$search_filter',
    pushmode = $pushmode,
	search_layout = '$search_layout' 	
	WHERE section_id = '$section_id' and page_id = '$page_id'";
$database->query($query);

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

?>