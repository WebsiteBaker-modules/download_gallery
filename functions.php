<?php

/* 
	Copyright and more information see file info.php
 */
 
 
 
// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));

// General Functions (used in multiple files)
// General:			Function to be used later in the code


if(!function_exists('microtime_float')){
	function microtime_float() {
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}
}


/**
	function: human_file_size
	
	make human readable filesize (bytes)
	
*/
if(!function_exists('human_file_size')){
	function human_file_size($bytes, $precision = 2)
	{
		$name = array('Bytes','KB','MB','GB','TB');
		
		if (!is_numeric($bytes) || $bytes < 0) 
			return false;
			
		for ($level = 0; $bytes >= 1024; $level++) 
			$bytes /= 1024;
		
		return round($bytes, $precision) . ' ' . $name[$level];
	}
}

if(!function_exists('init_fields')){
	function init_fields(&$header, &$footer, &$file_header, &$files_loop, &$file_footer, &$gloop, &$search_layout, &$gheader, &$gfooter) {
	   $header = '[SEARCH]';
	   
	   $footer = '<tr><td colspan="4">&nbsp;</td></tr>
	   <tr>
	   <td colspan="4" class="mod_download_gallery_th_f">
	   <table style="width:98%;display: [DISPLAY_PREVIOUS_NEXT_LINKS]">
	   <tr>
	   <td width="35%" align="left">[PREVIOUS_PAGE_LINK]</td>
	   <td width="30%" align="center">[OF]</td>
	   <td width="35%" align="right">[NEXT_PAGE_LINK]</td>
	   </tr>
	   </table>
	   </td>
	   </tr>
	   </table>';
	   
	   $file_header = '<table>
	   <tr>
	   <td class="mod_download_gallery_th_f"> [THTITLE] </td>
	   <td class="mod_download_gallery_th_f"> [THCHANGED] </td>
	   <td class="mod_download_gallery_th_f"> [THSIZE] </td>
	   <td class="mod_download_gallery_th_f"> [THCOUNT]  </td>
	   </tr>';
	   
	   $files_loop = '<tr>
	   <td class="mod_download_gallery_line_f"><img src="[FTIMAGE]" alt="" /> <a href="[LINK]" target="dlg"><b>[TITLE]</b></a></td>
	   <td class="mod_download_gallery_line_rightalign_f"> [DATE]</td>
	   <td class="mod_download_gallery_line_rightalign_f"> [SIZE]</td>
	   <td class="mod_download_gallery_line_rightalign_f"> [DL] </td>
	   </tr>
	   <tr>
	   <td class="mod_download_gallery_line_text_f" colspan="4">[DESCRIPTION]</td>
	   </tr>';
	   
	   $file_footer = '';
	   $gheader = '';
	   $gfooter = '';
	   
	   $gloop = '<tr>
	   <td colspan="4">&nbsp;</td>
	   </tr>
	   <tr>
	   <td class="mod_download_gallery_dgheader_f" colspan="4">[GROUPTITLE]</td>
	   </tr>';
	   
	   $search_layout = '[SEARCHBOX] [SEARCHSUBMIT] [SEARCHRESULT]';
	   return true;
	}
}

if(!function_exists('make_dl_dir')){
	function make_dl_dir() {
	   make_dir(WB_PATH.MEDIA_DIRECTORY.'/download_gallery/');
	
	   // add .htaccess file to /media/download_gallery folder if not already exist
	   if (!file_exists(WB_PATH . MEDIA_DIRECTORY . '/download_gallery/.htaccess')
		  or (filesize(WB_PATH . MEDIA_DIRECTORY . '/download_gallery/.htaccess') < 90))
	   {
		  // create a .htaccess file to prevent execution of PHP, HMTL files
		  $content = <<< EOT
	<Files .htaccess>
		order allow,deny
		deny from all
	</Files>
	
	<Files ~ "\.(php|pl)$">  
	ForceType text/plain
	</Files>
	
	Options -Indexes -ExecCGI
EOT;
	
		  $handle = fopen(WB_PATH . MEDIA_DIRECTORY . '/download_gallery/.htaccess', 'w');
		  fwrite($handle, $content);
		  fclose($handle);
		  change_mode(WB_PATH . MEDIA_DIRECTORY . '/download_gallery/.htaccess', 'file');
	   };
	}
}

/**
	function: dg_change_position	
	
	$direction: up || down
	$id:		file_id || group_id
	$table:		file || group
*/
if(!function_exists('dg_change_position')){
	function dg_change_position( $direction, $id, $table = 'file')
	{
	
		global $ordering, $page_id, $section_id;
			
		$baselink = WB_URL.'/modules/download_gallery/move_%s.php?page_id=%d&amp;section_id=%d&amp;';
		
		if($table == 'file'){
		
			$link = $baselink.'file_id=%d';
			
			// work out if we will use move_up or move_down file
			if($ordering == 'ASC')		
				$move_direction = ($direction == 'up') 	? 'up' : 'down';
			elseif($ordering == 'DESC')
				$move_direction = ($direction == 'down')? 'up' : 'down';
					
		}elseif($table == 'group'){
		
			$link = $baselink.'group_id=%d';
			$move_direction = $direction;
			
		}else return;	
			
		return(sprintf($link, $move_direction, $page_id, $section_id, $id));
	}
}


/**
	special reorder for stupid group_id '0'
	this function was introduced to handle file positions correctly
*/
if(!function_exists('reorder_id_null_group')){
	
	function reorder_id_null_group($table, $section_id) 
	{
		global $database;
		// Loop through all records and give new order
		$get_all = $database->query(sprintf("SELECT * FROM `%s` WHERE `group_id` = '0' AND `section_id` = '%d' ORDER BY `position` ASC", $table, $section_id));
		if($get_all->numRows() > 0) {
			$i = 0;
		
			while($row = $get_all->fetchRow()) {
				$i++;
				// Update each row with new order
				$database->query("UPDATE `".$table."` SET `position` = '$i' WHERE `file_id` = '".$row['file_id']."' AND `group_id` = '0' AND `section_id` = '".$section_id."'");				
				
			}	
				
		} else return true;
		
	}
	
}


/*
function human_file_size($size) {
   $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
   return round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i];
}


function hfs($size, $roundup, $decimals) {
   $filesizename = array(" Bytes", " kB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
   if (($roundup > 0) && ($decimals == 0)) {
		$addition=.45;
	} else {
		$addition = 0 ;
   }

   if ($size == 0) {
		$retstring = "0 kB";
	} else {
		$retstring = round($size/pow(1024, ($i = floor(log($size, 1024))))+$addition, $decimals) . $filesizename[$i];
   }
	
   // In DE Komma statt Punkt:
   if (LANGUAGE == "DE") {
      //echo "<!-- DEBUG DE -->\n";
      return str_replace('.', ',', $retstring);
   } else {
      //echo "<!-- DEBUG another language -->\n";
      return $retstring;
   }
}
*/
?>