<?php
/*
 * Copyright and more information see file info.php
 */

require('../../config.php');

// check if this file was invoked by the expected module file
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if ($referer && strpos($referer, WB_URL . '/modules/download_gallery/modify_settings.php') === false) {
        die(header('Location: ../../index.php'));
}
// include the admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
$admin_header = false;
// include the admin wrapper script
require(WB_PATH . '/modules/admin.php');
$admin = new admin('Pages', '', false, false);


// Load Language file
if (is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
if (is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
if (is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}
require(WB_PATH.'/framework/functions.php');

if (isset($_GET['fileext_id'])) {
        $fileext_id = (int) $_GET['fileext_id'];
}

// Query the file extension
$query_fileext         = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_file_ext WHERE fileext_id = '$fileext_id' AND section_id = '$section_id' AND page_id = '$page_id'");
$extdetails         = $query_fileext->fetchRow();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
                <title><?php echo $DGTEXT['MOD_TITLE']; ?></title>
                <link href="<?php echo WB_URL; ?>/modules/download_gallery/backend.css" rel="stylesheet" type="text/css" />
                <style type="text/css">
                .modify_section {
                    margin-left : 10px;
                }
                .modify_section h1 {
                    text-transform : none;
                    text-align     : left;
                    color          : white;
                }
                </style>
                <script language="JavaScript"  type="text/javascript">
                function validateForm(theForm) {

                var checkOK                = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789$, ";
                var checkStr        = theForm.file_ext.value;
                var allValid        = true;

                for (i = 0;  i < checkStr.length;  i++) {
                        ch = checkStr.charAt(i);
                        for (j = 0;  j < checkOK.length;  j++)
                                if (ch == checkOK.charAt(j))
                                break;
                                if (j == checkOK.length) {
                                        allValid = false;
                                        break;
                                }
                        }

                        if (!allValid) {
                                alert("Please enter only letter and numeric characters in the \"File Extensions\" field.\n\nThese extensions should be seperated with a comma.");
                                theForm.file_ext.focus();
                                return false;
                        }
                }
                </script>
        </head>

        <body>
                <div class="w3-container w3-padding-0 w3-blue">
                        <h1><?php echo $DGTEXT['MOD_FILE_EXT']; ?></h1>
                        <p><?php echo $DGTEXT['MOD_TXT']; ?></p>

                        <form name="modify_file_ext" method="post" action="<?php echo WB_URL; ?>/modules/download_gallery/save_extsettings.php" onsubmit="return validateForm(this);" >
                                <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
                                <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
                                <input type="hidden" name="fileext_id" value="<?php echo $extdetails['fileext_id']; ?>" />
                                <table style="width:100%">
                                        <tr>
                                                <td width="150"><?php echo $DGTEXT['FILE_TYPE']; ?>:</td>
                                                <td><strong><?php echo $extdetails['file_type']; ?></strong></td>
                                        </tr>
                                        <tr>
                                                <td><?php echo $DGTEXT['FILE_TYPE_EXT']; ?>:</td>
                                        </tr>
                                        <tr>
                                                <td colspan="2">
                                                                <textarea name="file_ext" style="width: 96%; height: 100px;"><?php echo str_replace(",",", ", $extdetails['extensions']); ?></textarea>
                                                </td>
                                        </tr>
                                </table>
                                <table style="width:100%">
                                        <tr>
                                                <td align="center">
                                                        <input name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" class="btn w3-theme-d5 w3-hover-green w3-padding-4 w3-border-theme" style="margin-top: 5px;" /> &nbsp;
                                                        <input type="button" value="<?php echo $TEXT['CANCEL']; ?>" onclick="window.close(); return false;" class="btn w3-theme-d5 w3-hover-green w3-padding-4 w3-border-theme" style="margin-top: 5px;" />
                                                </td>
                                        </tr>
                                </table>
                        </form>
                </div>
        </body>
</html>