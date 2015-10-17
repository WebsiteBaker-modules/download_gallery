<?php
/* 
 * Copyright and more information see file info.php
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

<table class="row_a" cellpadding="2" cellspacing="0" border="0" width="100%">
	<tr>
		<td colspan="2"><strong><?php echo $TEXT['MODIFY'].'/'.$TEXT['ADD'].' '.$TEXT['GROUP']; ?></strong></td>
	</tr>
	<tr>
		<td width="80"><?php echo $TEXT['TITLE']; ?>:</td>
		<td>
			<input type="text" name="title" value="<?php echo $fetch_content['title']; ?>" style="width: 98%;" maxlength="255" />
		</td>
	</tr>
	<tr>
		<td><?php echo $TEXT['ACTIVE']; ?>:</td>
		<td>
			<input type="radio" name="active" id="active_true" value="1" <?php if($fetch_content['active'] == 1) { echo ' checked="checked"'; } ?> />
			<a href="#" onclick="javascript: document.getElementById('active_true').checked = true;">
			<?php echo $TEXT['YES']; ?>
			</a>
			&nbsp; &nbsp; 
			<input type="radio" name="active" id="active_false" value="0" <?php if($fetch_content['active'] == 0) { echo ' checked="checked"'; } ?> />
			<a href="#" onclick="javascript: document.getElementById('active_false').checked = true;">
			<?php echo $TEXT['NO']; ?>
			</a>
		</td>
	</tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td align="left">
			<input name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" style="width: 100px; margin-top: 5px;" />
		</td>
		<td align="right">
			<input type="button" value="<?php echo $TEXT['CANCEL']; ?>" onclick="javascript: window.location = '<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id; ?>';" style="width: 100px; margin-top: 5px;" />
		</td>
	</tr>
</table>
</form>
<?php
// Print admin footer
$admin->print_footer();

?>