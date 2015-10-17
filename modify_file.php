<?php
/* 
 * CMS module: Download Gallery 2
 * Copyright and more information see file info.php
 */

require('../../config.php');

// Get id
$file_id = '';
if(!isset($_GET['file_id']) OR !is_numeric($_GET['file_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$file_id = $_GET['file_id'];
}

// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');
require(WB_PATH.'/framework/functions.php');

if(LANGUAGE_LOADED) {
	require_once(WB_PATH.'/modules/download_gallery/languages/EN.php');
	if (file_exists (WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php')) {
		require_once(WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php');
	}
}

// Get header and footer
$query_content = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_files WHERE file_id = '$file_id' and page_id = '$page_id'");
$fetch_content = $query_content->fetchRow();

// General File Information
$fname = $fetch_content['filename'];

if($fname == '') {
	$fname = 'dummy_file_name_wb.ext';
  $remotelink = '';
} elseif ((strpos($fname, ':/') > 1)) {
  $remotelink = $fname;
	$fname = 'dummy_file_name_wb.ext';
} else {
  $remotelink = '';
}

if (!defined('WYSIWYG_EDITOR') OR WYSIWYG_EDITOR=="none" OR !file_exists(WB_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php')) {
	function show_wysiwyg_editor($name,$id,$content,$width,$height) {
		echo '<textarea name="'.$name.'" id="'.$id.'" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
	}
} else {
	$id_list=array("content");
	require(WB_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php');
}
?>

<form name="modify" action="<?php echo WB_URL; ?>/modules/download_gallery/save_file.php" method="post" enctype="multipart/form-data" style="margin: 0;">

	<input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
	<input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
	<input type="hidden" name="file_id" value="<?php echo $file_id; ?>" />
	<input type="hidden" name="link" value="<?php echo $fetch_content['link']; ?>" />

	<table class="row_a" cellpadding="2" cellspacing="0" border="0" width="100%">
		<tr>
			<td colspan="2"><strong><?php echo $TEXT['MODIFY'].'/'.$TEXT['DELETE'].' '.$TEXT['FILE']; ?></strong></td>
		</tr>
		<tr>
			<td valign="top" width="25%"><?php echo $TEXT['TITLE']; ?>:</td>
			<td valign="top">
				<input type="text" name="title" value="<?php echo stripslashes($fetch_content['title']); ?>" style="width: 98%;" maxlength="255" />
			</td>
		</tr>

    <!-- local file: -->
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['LOKALFILE']; ?>:</td>
			<?php
			if(file_exists(WB_PATH.MEDIA_DIRECTORY.'/download_gallery/' .$fname )) {
				?>
				<td valign="top">
					<b><?php echo $fname; ?></b><br />
					<input type="checkbox" name="delete_file" id="delete_file" value="yes" /><?php echo $TEXT['DELETE']; ?>
					<br />
					<input type="checkbox" name="delete_counter" id="delete_counter" value="yes" /><?php echo $DGTEXT['RESET_Counter']; ?>
				</td>
				<?php
			} elseif(trim($remotelink)!=""){
				?>
				<td valign="top">
					<input type="file" name="file" />
				</td>
				<?php
			} elseif(trim($fetch_content['filename'])!=""){
				?>
				<td valign="top">
					<b><input type="hidden" name="existingfile"  value="<?php echo $fetch_content['link'];?>"><?php echo $fetch_content['link'];?></b>
					<input type="checkbox" name="delete_file2" id="delete_file2" value="yes" /><?php echo $TEXT['DELETE']; ?>
				</td>
				<?php
			} else {
				?>
				<td valign="top">
					<input type="file" name="file" />
				</td>
				<?php
			}
			?>
		</tr>
		<?php if($fetch_content['filename']==""){ ?>
			<tr>
				<td valign="top" width="25%"><?php echo $DGTEXT['EXISTINGFILE']; ?>:</td>
				<td valign="top">
					<select name="existingfile" style="width: 99%;">
					<option value=''>&nbsp;</option>
					<?php
					$folder_list=directory_list(WB_PATH.MEDIA_DIRECTORY);
					array_push($folder_list,WB_PATH.MEDIA_DIRECTORY);
					sort($folder_list);
					foreach($folder_list AS $name) {
						$file_list=file_list($name);
						sort($file_list);
						foreach($file_list AS $filename) {
							$thumb_count=substr_count($filename, '/thumbs/');
							if($thumb_count==0){
								echo "<option value='".WB_URL.str_replace(WB_PATH,'',$filename)."'>".str_replace(WB_PATH.MEDIA_DIRECTORY,'',$filename)."</option>\n";
							}
							$thumb_count="";
						}
					}
					?>
					</select>
				</td>
			</tr>
		<?php } ?>

    <!-- alternativ: Remote Link (no Upload) -->
		<tr>
	  		<td><?php echo $DGTEXT['REMOTE_LINK']; ?>:</td>
	  		<td><input type="text" name="remote_link" value="<?php echo $remotelink; ?>" style="width: 98%;" maxlength="255" /></td>
    </tr>

		<tr>
	  		<td width="80"><?php echo $TEXT['GROUP']; ?>:</td>
	  		<td>
	  			<select name="group" style="width: 98%;">
				<option value="0"><?php echo $TEXT['NONE']; ?></option>
				<?php
				$query = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_groups WHERE section_id = '$section_id' ORDER BY position ASC");
				if($query->numRows() > 0) {
					// Loop through groups
					while($group = $query->fetchRow()) {
						?>
						<option value="<?php echo $group['group_id']; ?>"<?php if($fetch_content['group_id'] == $group['group_id']) { echo ' selected'; } ?>><?php echo $group['title']; ?></option>
						<?php
					}
				}
				?>
				</select>
	  		</td>
		</tr>

		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['THRELEASED']; ?>:</td>
			<td valign="top">
				<input type="date" name="released" id="released" value="<?php if($fetch_content['released'] > 1) {echo date('d.m.Y', $fetch_content['released']);}?>" />
			</td>
		</tr>

		<tr>
			<td valign="top" width="25%"><?php echo $TEXT['ACTIVE']; ?>:</td>
			<td valign="top">
				<input type="radio" name="active" id="active_true" value="1" <?php if($fetch_content['active'] == 1) { echo ' checked="checked"'; } ?> />
				<a href="#" onclick="javascript: document.getElementById('active_true').checked = true;"><?php echo $TEXT['YES']; ?></a>
				&nbsp;
				<input type="radio" name="active" id="active_false" value="0" <?php if($fetch_content['active'] == 0) { echo ' checked="checked"'; } ?> />
				<a href="#" onclick="javascript: document.getElementById('active_false').checked = true;"><?php echo $TEXT['NO']; ?></a>
			</td>
		</tr>
		<?php if($fetch_content['title']==""){ ?>
		<tr>
			<td valign="top" width="25%"><?php echo $DGTEXT['OVERWRITE']; ?>:</td>
			<td valign="top">
				<input type="checkbox" name="overwrite" id="overwrite" value="yes" />
			</td>
		</tr>
		<?php } ?>
	</table>

	<table cellpadding="2" cellspacing="0" border="0" width="100%">
		<tr>
			<td>
				<?php
				show_wysiwyg_editor("description","description",htmlspecialchars($fetch_content['description']), "100%", "400");
				?>
			</td>
		</tr>
	</table>

	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td align="left">
				<input name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" style="width: 100px; margin-top: 5px;" />
			</td>
			<td align="right">
                <input type="button" value="<?php echo $TEXT['CANCEL']; ?>" onclick="javascript: window.location = '<?php
				echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id; ?>';" style="width: 100px; margin-top: 5px;" />
			</td>
		</tr>
	</table>
</form>
<?php
// Print admin footer
$admin->print_footer();

?>