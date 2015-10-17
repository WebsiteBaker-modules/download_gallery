<?php
/*
 * CMS module: Download Gallery 2
 * Copyright and more information see file info.php
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));
require('info.php');

// include core functions of WB 2.7 to edit the optional module CSS files (frontend.css, backend.css)
@include_once(WB_PATH .'/framework/module.functions.php');

if(LANGUAGE_LOADED) {
	require_once(WB_PATH.'/modules/download_gallery/languages/EN.php');
	if (file_exists (WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php')) {
		require_once(WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php');
	}
}

// check if backend.css file needs to be included into the <body></body> of modify.php
if(!method_exists($admin, 'register_backend_modfiles') && file_exists(WB_PATH .'/modules/download_gallery/backend.css')) {
	echo '<style type="text/css">';
	include(WB_PATH .'/modules/download_gallery/backend.css');
	echo "\n</style>\n";
}

// STEP 0:	initialize some variables
$page_id = (int) $page_id;
$section_id = (int) $section_id;

//delete empty records
$database->query("DELETE FROM ".TABLE_PREFIX."mod_download_gallery_files  WHERE page_id = '$page_id' and section_id = '$section_id' and title=''");
$database->query("DELETE FROM ".TABLE_PREFIX."mod_download_gallery_groups  WHERE page_id = '$page_id' and section_id = '$section_id' and title=''");
$areyousure= str_replace(' ', '%20', $TEXT['ARE_YOU_SURE']);

echo "\n<div class=\"download_gallery\">\n";
echo "<h2>$module_name - ".$TEXT['PAGE']." $page_id</h2>";
if(function_exists('edit_module_css')) {
	edit_module_css('download_gallery');
}

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td align="left" width="25%">
			<input type="button" value="<?php echo $TEXT['ADD'].' '.$TEXT['FILE']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/download_gallery/add_file.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
		</td>
		<td align="left" width="25%">
			<input type="button" value="<?php echo $TEXT['ADD'].' '.$TEXT['GROUP']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/download_gallery/add_group.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
		</td>
		<td align="right" width="25%">
			<input type="button" value="<?php echo $TEXT['SETTINGS']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/download_gallery/modify_settings.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
		</td>
		<td align="right" width="25%">
			<input type="button" value="<?php echo $MENU['HELP']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/download_gallery/help.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
		</td>
	</tr>
</table>

<br />

<h2><?php echo $TEXT['MODIFY'].'/'.$TEXT['DELETE'].' '.$TEXT['FILE']; ?></h2>

<?php
// Get settings
$query_settings = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_settings WHERE section_id = '$section_id'");
$settings = $query_settings->fetchRow();

$extordering=$settings['extordering'];
$orderkey=$settings['ordering'];
if($orderkey =='2' or $orderkey == '3'){
	$orderby="title";
} else {
	$orderby="position";
}

if ($orderkey == '0' or $orderkey == '2') {
	$ordering = 'ASC';
	$moveupfile='move_up.php';
	$movedownfile='move_down.php';
} else {
	$ordering = 'DESC';
	$moveupfile='move_down.php';
	$movedownfile='move_up.php';
}
///set the extension order,but  position order over rides
if ($extordering==0 and $orderby!="position"){$extorder=" extension ASC, ";}elseif($extordering==1 and $orderby!="position"){$extorder=" extension DESC,";}else{$extorder="";}

// Loop through existing files

$query_files = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_download_gallery_files` WHERE section_id = '$section_id' ORDER BY $extorder $orderby $ordering");
if($query_files->numRows() > 0) {
	$num_files = $query_files->numRows();
	$row = 'a';
	?>
	<table cellpadding="2" cellspacing="0" border="0" width="100%">
	<?php
	while($post = $query_files->fetchRow()) {
			$position=$post['position'];
		if ($settings['ordering'] == '0') {;}
			else {
				$position = $num_files-$position+1;
			};
		?>
		<tr class="row_<?php echo $row; ?>" style="height: 20px;">
			<td width="20" style="padding-left: 5px;">
				<a href="<?php echo WB_URL; ?>/modules/download_gallery/modify_file.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;file_id=<?php echo $post['file_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
				</a>
			</td>
			<td>
				<a href="<?php echo WB_URL; ?>/modules/download_gallery/modify_file.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;file_id=<?php echo $post['file_id']; ?>">
					<?php echo stripslashes($post['title']); ?>
				</a>
			</td>
			<td width="150">
				<?php echo $TEXT['GROUP'].': ';
				// Get group title
				$query_title = $database->query("SELECT title FROM ".TABLE_PREFIX."mod_download_gallery_groups WHERE group_id = '".$post['group_id']."'");
				if($query_title->numRows() > 0) {
					$fetch_title = $query_title->fetchRow();
					echo $fetch_title['title'];
				} else {
					echo $TEXT['NONE'];
				}
				?>
			</td>
			<td width="80">
				<?php echo $TEXT['ACTIVE'].': '; if($post['active'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; } ?>
			</td>
			<td width="20">
				<?php if($position != 1 and $orderby=="position") { ?>
					<a href="<?php echo WB_URL; ?>/modules/download_gallery/<?php echo $moveupfile; ?>?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;file_id=<?php echo $post['file_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
						<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
					</a>
				<?php } ?>
			</td>
			<td width="20">
				<?php if($position != $num_files and $orderby=="position") { ?>
					<a href="<?php echo WB_URL; ?>/modules/download_gallery/<?php echo $movedownfile; ?>?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;file_id=<?php echo $post['file_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
						<img src="<?php echo THEME_URL; ?>/images/down_16.png" border="0" alt="v" />
					</a>
				<?php } ?>
			</td>
			<td width="20">
				<a href="javascript:confirm_link('<?php echo $areyousure ."','". WB_URL; ?>/modules/download_gallery/delete_file.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;file_id=<?php echo $post['file_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/delete_16.png" border="0" alt="X" />
				</a>
			</td>
		</tr>
		<?php
		// Alternate row color
		if($row == 'a') {
			$row = 'b';
		} else {
			$row = 'a';
		}
	}
	?>
	</table>
	<?php
} else {
	echo $TEXT['NONE_FOUND'];
}

?>
<br />

<h2><?php echo $TEXT['MODIFY'].'/'.$TEXT['DELETE'].' '.$TEXT['GROUP']; ?></h2>

<?php
echo mysql_error();
// Loop through existing links
$query_groups = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_download_gallery_groups` WHERE section_id = '$section_id' ORDER BY position ASC");
if($query_groups->numRows() > 0) {
	$row = 'a';
	?>
	<table cellpadding="2" cellspacing="0" border="0" width="100%">
		<?php while($group = $query_groups->fetchRow()) { ?>
			<tr class="row_<?php echo $row; ?>" style="height: 20px;">
				<td width="20" style="padding-left: 5px;">
					<a href="<?php echo WB_URL; ?>/modules/download_gallery/modify_group.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;group_id=<?php echo $group['group_id']; ?>">
						<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
					</a>
				</td>
				<td>
					<a href="<?php echo WB_URL; ?>/modules/download_gallery/modify_group.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;group_id=<?php echo $group['group_id']; ?>">
						<?php echo $group['title']; ?>
					</a>
				</td>
				<td width="80">
					<?php echo $TEXT['ACTIVE'].': '; if($group['active'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; } ?>
				</td>
				<td width="20">
					<a href="<?php echo WB_URL; ?>/modules/download_gallery/move_up.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;group_id=<?php echo $group['group_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
						<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
					</a>
				</td>
				<td width="20">
					<a href="<?php echo WB_URL; ?>/modules/download_gallery/move_down.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;group_id=<?php echo $group['group_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
						<img src="<?php echo THEME_URL; ?>/images/down_16.png" border="0" alt="v" />
					</a>
				</td>
				<td width="20">
					<a href="#" onclick="javascript:confirm_link('<?php echo $areyousure ."','". WB_URL; ?>/modules/download_gallery/delete_group.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;group_id=<?php echo $group['group_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
						<img src="<?php echo THEME_URL; ?>/images/delete_16.png" border="0" alt="X" />
					</a>
				</td>
			</tr>
			<?php
			// Alternate row color
			if($row == 'a') {
				$row = 'b';
			} else {
				$row = 'a';
			}
		}
		?>
		</table>
	<?php
} else {
	echo $TEXT['NONE_FOUND'];
}
?>
</div>
