<?php
/* 
 * Copyright and more information see file info.php
 */

require('../../config.php');
// include the admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
$admin_header = false;
require(WB_PATH.'/modules/admin.php');

// Load Language file
if (is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
if (is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
if (is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

require(WB_PATH.'/framework/functions.php');

$fileext_id = '';
if (isset($_POST['fileext_id'])) {
	$fileext_id = (int) $_POST['fileext_id'];
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php echo $DGTEXT['MOD_TITLE']; ?></title>
		<link href="<?php echo WB_URL; ?>/modules/download_gallery/backend.css" rel="stylesheet" type="text/css" />
		<style type="text/css">
		.modify_section {
			margin-left	: 10px;
		}
		.modify_section h1 {
			text-transform	: none;
			text-align		: left;	
			color			: white;
		}
		</style>
	</head>
	<body>
		<div  class="w3-container w3-padding-0 w3-blue">
			<h1><?php echo $DGTEXT['MOD_FILE_EXT']; ?></h1>
			<table>
				<tr>
					<td>
					<?php
					$checkOK	= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789$, ";
					$checkStr	= $_POST['file_ext'];
					$allValid	= true;
					//Loop through string and see if illegal chars are used
					for ($i = 0;  $i < strlen($checkStr);  $i++) {
						$ch = substr($checkStr, $i, 1);
						if (strpos($checkOK, $ch)===FALSE) {
							$allValid = false;
							break;
						}
					}	
					// Check if string was valid
					if (!$allValid) {
						echo "Please enter only letter and numeric characters in the 'File Extensions' field. These extensions should be seperated with a comma.";
					}
					else {
						//Remove the spaces
						$checkStr = str_replace(" ","", $checkStr);
						//Update the database
						$database->query("UPDATE ".TABLE_PREFIX."mod_download_gallery_file_ext "
							. " SET extensions = '$checkStr' " 
							. " WHERE fileext_id = '$fileext_id' and page_id = '$page_id'");
						echo $DGTEXT['FILE_STORED'];
					}
					?>
					</td>
				</tr>
				<tr>
					<td style="text-align: center; height: 200px;">
					<form action="save_extsettings.php" method="post">
					<input type="button" value="<?php echo $TEXT['CLOSE']; ?>" onclick="window.close(); return false;" class="btn w3-theme-d5 w3-hover-green w3-padding-4 w3-border-theme" style="margin-top: 5px;" />
					</form>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>