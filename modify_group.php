<?php
/* 
	Copyright and more information see file info.php
 */
/*
 changes by Stefek:
 
 - restyled the template
 
*/
require('../../config.php');

// Get id
$group_id = '';
if(!isset($_GET['group_id']) OR !is_numeric($_GET['group_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$group_id = $_GET['group_id'];
}

// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');

// Get header and footer
$query_content = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_groups WHERE group_id = '$group_id' and page_id = '$page_id'");
$fetch_content = $query_content->fetchRow();

?>

<form name="modify" action="<?php echo WB_URL; ?>/modules/download_gallery/save_group.php" method="post" style="margin: 0;">

<input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
<input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />

<table class="settings_table" style="width:100%">
	<caption><?php echo $TEXT['MODIFY'].'/'.$TEXT['ADD'].' '.$TEXT['GROUP']; ?></caption>	
	<tr>
		<th><?php echo $TEXT['ACTIVE']; ?>:</th>
		<td>
			<input type="radio" name="active" id="active_true" value="1" <?php if($fetch_content['active'] == 1) echo ' checked="checked"'; ?> />
			<label for="active_true"><?php echo $TEXT['YES']; ?></label>			
			<input type="radio" name="active" id="active_false" value="0" <?php if($fetch_content['active'] == 0) echo ' checked="checked"'; ?> />
			<label for="active_false"><?php echo $TEXT['NO']; ?></label>
		</td>
	</tr>
	<tr>
		<th><?php echo $TEXT['GROUP']; ?>-<?php echo $TEXT['TITLE']; ?>:</th>
		<td>
			<input type="text" id="title" name="title" value="<?php echo stripslashes($fetch_content['title']); ?>" style="width: 98%;font-size:12pt; font-weight:bold;" maxlength="255" />
		</td>
	</tr>
	
	<tfoot>
	<tr>
		<td style="text-align:left;">
			<input name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" class="btn w3-theme-d5 w3-hover-green w3-padding-4 w3-border-theme" style="margin-top: 5px;" />
		</td>
		<td style="text-align:right;">
			<input type="button" value="<?php echo $TEXT['CANCEL']; ?>" onclick="javascript: window.location = '<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id; ?>';" class="btn w3-theme-d5 w3-hover-green w3-padding-4 w3-border-theme" style="margin-top: 5px;" />
		</td>
	</tr>
	</tfoot>
</table>
</form>

<?php
if (empty($fetch_content['title']))
	echo '<script type="text/javascript">document.getElementById("title").focus();</script>';

// Print admin footer
$admin->print_footer();

?>