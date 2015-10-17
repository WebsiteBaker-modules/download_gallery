<?php
/* 
 * Copyright and more information see file info.php
 */

require('../../config.php');
require(WB_PATH.'/modules/admin.php');	

// Load Language file
if(LANGUAGE_LOADED) {
	require_once(WB_PATH.'/modules/download_gallery/languages/EN.php');
	if (file_exists (WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php')) {
		require_once(WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php');
	}
}

// Get General Settings
$query_content = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_settings WHERE section_id = '$section_id' and page_id = '$page_id'");
$fetch_content = $query_content->fetchRow();

// List Extension types
$query_fileext 	= $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_file_ext WHERE section_id = '$section_id' and page_id = '$page_id'");

?>

<script type="text/javascript">
//<![CDATA[
function showpopup(URL, w, h) {
	day = new Date();
	id = day.getTime();
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width='+w+',height='+h+',left='+winl+',top='+wint);");
}

function process(element){
	switch(element.value){
		case "0":
			document.getElementById('extorder').style.display = "none";
			break;
		case "1":
			document.getElementById('extorder').style.display = "";
			break;
	}
}

function process2(element){
	switch(element.value){
		case "0":
			document.getElementById('tr_captcha').style.display = "none";
			break;
		case "1":
		case "2":
			document.getElementById('tr_captcha').style.display = "";
			break;
	}
}
//]]>
</script>

<form name="modify" action="<?php echo WB_URL; ?>/modules/download_gallery/save_settings.php" method="post" style="margin: 0;">

	<input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
	<input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />

	<table class="row_a" cellpadding="2" cellspacing="0" border="0" width="100%">
		<tr>
			<td colspan="2"><strong><?php echo $DGTEXT['GSETTINGS']; ?></strong></td>
		</tr>
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['FILES_PER_PAGE']; ?>:</td>
			<td valign="top"><input type="text" name="files_per_page" value="<?php echo $fetch_content['files_per_page']; ?>" style="width: 30px" /> 0 = <?php echo $TEXT['UNLIMITED']; ?></td>
		</tr>
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['FILE_ROUNDUP']; ?>:</td>
			<td valign="top">
		        <?php
		        if ($fetch_content['file_size_roundup'] == '1') {
		            $checked = 'checked="checked"';
		        } else {
		            $checked = '';
		        }
		        ?>
		        <input type="checkbox" value="1" name="file_size_round" <?php echo $checked; ?> />
		    </td>
		</tr>
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['SEARCHFILTER']; ?>:</td>
			<td valign="top">
		        <?php
		        if ($fetch_content['search_filter'] == '1') {
		            $checked = 'checked="checked"';
		        } else {
		            $checked = '';
		        }
		        ?>
		        <input type="checkbox" value="1" name="search_filter" <?php echo $checked; ?> />
		    </td>
		</tr>
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['FILE_DECIMALS']; ?>:</td>
			<td valign="top">
				<?php $decicount = stripslashes($fetch_content['file_size_decimals']); ?>
				<?php if ($decicount == "") { $decicount = 0; } ?>
				<select name="file_size_decimals" style="width: 50px">
					<option value ="0" <?php if ($decicount == 0) { echo "selected='selected'"; } ?> >0</option>
					<option value ="1" <?php if ($decicount == 1) { echo "selected='selected'"; } ?> >1</option>
					<option value ="2" <?php if ($decicount == 2) { echo "selected='selected'"; } ?> >2</option>
					<option value ="3" <?php if ($decicount == 3) { echo "selected='selected'"; } ?> >3</option>
					<option value ="4" <?php if ($decicount == 4) { echo "selected='selected'"; } ?> >4</option>
				</select>
		    </td>
		</tr>
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['FILE_TYPE_EXT']; ?>:</td>
			<td valign="top">
				<table width="98%">
				<?php
				if($query_fileext->numRows() > 0) {
					while($fileext = $query_fileext->fetchRow()) {
					?>
					<tr>
						<td width="20" style="padding-left: 5px;">
							<a href="javascript:showpopup('<?php echo WB_URL; ?>/modules/download_gallery/modify_extensions.php?leptokh=#-!leptoken-!#&amp;page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;fileext_id=<?php echo $fileext['fileext_id']; ?>',800,400)" title="<?php echo $TEXT['MODIFY']; ?>">
								<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
							</a>
						</td>
						<td><?php echo "Type: " . $fileext['file_type']; ?></td>
						<td><?php
							$temp = (strlen($fileext['extensions']) > 55) ? "..." : "";
							echo substr($fileext['extensions'], 0, 55) . $temp;?>
						</td>
					</tr>
					<?php
					}
				}
				?>
				</table>
			</td>
		</tr>
		<?php
		/*
		['ordering']
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
		?>
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['ORDERING']; ?>:</td>
			<td valign="top">
				<select name="ordering" style="width: 200px">
					<?php
					if (
					$fetch_content['ordering'] == '0' or $fetch_content['ordering'] == '2' ) {
						$selected_asc = 'selected="selected"';
						$selected_desc = '';
					} else {
						$selected_asc = '';
						$selected_desc = 'selected="selected"';
					}
					?>
					<option value="0" <?php echo $selected_asc; ?>><?php echo $DGTEXT['ASCENDING']; ?></option>
					<option value="1" <?php echo $selected_desc; ?>><?php echo $DGTEXT['DESCENDING']; ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['ORDERBY']; ?>:</td>
			<td valign="top">
				<select name="orderby" style="width: 200px" onchange="process(this);">
	            <?php
	    		if ($fetch_content['ordering'] == '0' or $fetch_content['ordering'] == '1')	{
	                $selected_position = 'selected="selected"';
	                $selected_title = '';
	                $visible='none';
				} else {
	                $selected_position = '';
	                $selected_title = 'selected="selected"';
  	                $visible='';
				}
	            ?>
	            <option value="0" <?php echo $selected_position; ?>><?php echo $DGTEXT['POSITION']; ?></option>
	            <option value="1" <?php echo $selected_title; ?>><?php echo $DGTEXT['TITLE']; ?></option>
				</select>
			</td>
		</tr>
		<tr id="extorder" style="display:<?php echo $visible; ?>;">
			<td valign="top" width="25%"><?php echo $DGTEXT['EXTORDERING']; ?>:</td>
			<td valign="top">
				<select name="extordering" style="width: 200px">
	            <?php
	            if ( $fetch_content['extordering'] == '0' or $fetch_content['extordering'] == '' ) {
	                $extselected_asc = 'selected="selected"';
	                $extselected_desc = '';
	                $extselected_none = '';
	            } elseif ($fetch_content['extordering'] == '1' ) {
	                $extselected_asc = '';
	                $extselected_desc = 'selected="selected"';
	                $extselected_none = '';
	            } else {
   	                $extselected_asc = '';
	                $extselected_desc = '';
	                $extselected_none = 'selected="selected"';
				}
	            ?>
	            <option value="9" <?php echo $extselected_none; ?>><?php echo $DGTEXT['NOSORT']; ?></option>
	            <option value="0" <?php echo $extselected_asc; ?>><?php echo $DGTEXT['ASCENDING']; ?></option>
	            <option value="1" <?php echo $extselected_desc; ?>><?php echo $DGTEXT['DESCENDING']; ?></option>
				</select> <?php echo $DGTEXT['EXTINFO']; ?>
			</td>
		</tr>
		
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['PUSHMODE']; ?>:</td>
			<td valign="top">
				<select name="pushmode" style="width: 200px">
	            <?php
	    		if ($fetch_content['pushmode'] == '0')	{
	                $selected_0 = 'selected="selected"';
	                $selected_1 = '';
				} else {
	                $selected_0 = '';
	                $selected_1 = 'selected="selected"';
				}
	            ?>
	            <option value="0" <?php echo $selected_0; ?>><?php echo $DGTEXT['DL_view']; ?></option>
	            <option value="1" <?php echo $selected_1; ?>><?php echo $DGTEXT['DL_save']; ?></option>
				</select><br />
				<?php echo $DGTEXT['PM_HELP']; ?><br />
			</td>
		</tr>
		
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['USERUPLOAD']; ?>:</td>
			<td valign="top">
				<?php
					$uploadpub="";
					$uploadreg="";
					$uploadno="";
				if ($fetch_content['userupload'] == '1') {
					$uploadpub="checked='checked'";
	                $visible2 = '';
				} elseif ($fetch_content['userupload'] == '2') {
					$uploadreg="checked='checked'";
	                $visible2 = '';
				}else {
					$uploadno="checked='checked'";
	                $visible2 = 'none';
				}
				?>
				<input type="radio" name="userupload" class="userupload" value="0" <?php echo $uploadno; ?>  onchange="process2(this);" /><?php echo $DGTEXT['NOT']; ?>
				<input type="radio" name="userupload" class="userupload" value="2" <?php echo $uploadreg; ?> onchange="process2(this);" /><?php echo $DGTEXT['REGISTERED']; ?>
				<input type="radio" name="userupload" class="userupload" value="1" <?php echo $uploadpub; ?> onchange="process2(this);" /><?php echo $TEXT['PUBLIC']; ?>
			</td>
		</tr>
		<tr id="tr_captcha" style="display:<?php echo $visible2; ?>;">
			<td valign="top" width="25%"><?php echo $TEXT['CAPTCHA_VERIFICATION']; ?>:</td>
			<td valign="top">
				<?php
					$use_captcha_true_checked = '';
					$use_captcha_false_checked = '';
					if ($fetch_content['use_captcha'] == '1') {
						$use_captcha_true_checked = "checked='checked'";
					} else {
						$use_captcha_false_checked = "checked='checked'";
					}
				?>
				<input type="radio" name="use_captcha" id="use_captcha_true" value="1" <?php echo $use_captcha_true_checked;  ?> />
				<label for="use_captcha_true"><?php echo $TEXT['ENABLED']; ?></label>
				<input type="radio" name="use_captcha" id="use_captcha_false" value="0" <?php echo $use_captcha_false_checked;  ?> />
				<label for="use_captcha_false"><?php echo $TEXT['DISABLED']; ?></label>
			</td>
		</tr>
	</table>

	<table class="row_a" cellpadding="2" cellspacing="0" border="0" width="100%" style="margin-top: 8px;">
		<tr>
			<td colspan="2"><strong><?php echo $DGTEXT['LSETTINGS']; ?></strong></td>
		</tr>
		<tr>
			<td valign="top" width="25%"><?php echo $TEXT['HEADER']; ?>:</td>
			<td valign="top"><textarea cols="50" rows="5" name="header" style="width: 98%; height: 80px;"><?php echo htmlspecialchars($fetch_content['header']); ?></textarea></td>
		</tr>
		<tr>
			<td valign="top" width="25%"><?php echo $TEXT['FOOTER']; ?>:</td>
			<td valign="top"><textarea cols="50" rows="5" name="footer" style="width: 98%; height: 80px;"><?php echo htmlspecialchars($fetch_content['footer']); ?></textarea></td>
		</tr>
		<tr>
			<td class="newsection" valign="top" width="25%"><?php echo $TEXT['FILE'].' '.$TEXT['HEADER']; ?>:</td>
			<td class="newsection" valign="top"><textarea cols="50" rows="5" name="file_header" style="width: 98%; height: 60px;"><?php echo htmlspecialchars($fetch_content['file_header']); ?></textarea></td>
		</tr>
		<tr>
			<td valign="top" width="25%"><?php echo $TEXT['FILE'].' '.$TEXT['LOOP']; ?>:</td>
			<td valign="top"><textarea cols="50" rows="5" name="files_loop" style="width: 98%; height: 60px;"><?php echo htmlspecialchars($fetch_content['files_loop']); ?></textarea></td>
		</tr>
		<tr>
			<td valign="top"width="25%"><?php echo $TEXT['FILE'].' '.$TEXT['FOOTER']; ?>:</td>
			<td valign="top"><textarea cols="50" rows="5" name="file_footer" style="width: 98%; height: 60px;"><?php echo htmlspecialchars($fetch_content['file_footer']); ?></textarea></td>
		</tr>
		<tr>
			<td class="newsection" valign="top" width="25%"><?php echo $DGTEXT['GPHEADER']; ?></td>
			<td class="newsection" valign="top"><textarea cols="50" rows="5" name="gheader" style="width:98%; height: 60px;"><?php echo htmlspecialchars($fetch_content['gheader']); ?></textarea></td>
		</tr>
		<tr>
			<td valign="top"width="25%"><?php echo $DGTEXT['GPLOOP']; ?></td>
			<td valign="top"><textarea cols="50" rows="5" name="gloop" style="width:98%; height: 60px;"><?php echo htmlspecialchars($fetch_content['gloop']); ?></textarea></td>
		</tr>
		<tr>
			<td valign="top"width="25%"><?php echo $DGTEXT['GPFOOTER']; ?></td>
			<td valign="top"><textarea cols="50" rows="5" name="gfooter" style="width:98%; height: 60px;"><?php echo htmlspecialchars($fetch_content['gfooter']); ?></textarea></td>
		</tr>
		<tr>
			<td class="newsection" valign="top"width="25%"><?php echo $DGTEXT['SEARCHLAYOUT']; ?></td>
			<td class="newsection" valign="top"><textarea cols="50" rows="5" name="search_layout" style="width:98%; height: 60px;"><?php echo htmlspecialchars($fetch_content['search_layout']); ?></textarea></td>
		</tr>

	</table>

	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td align="left">
				<input name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" style="width: 100px; margin-top: 5px;" />
			</td>
			<td align="center">
				<input name="reset_table" type="submit" value="<?php echo $DGTEXT['RESET_TABLE']; ?>" style="margin-top: 5px;" />
			</td>
			<td align="right">
				<input type="button" value="<?php echo $TEXT['CANCEL']; ?>" onclick="javascript:window.location='<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id; ?>';" style="width: 100px; margin-top: 5px;" />
			</td>
		</tr>
	</table>
</form>
<?php
// Print admin footer
$admin->print_footer();
?>