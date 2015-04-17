<?php
/*
 * CMS module: Download Gallery 2
 * Copyright and more information see file info.php
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));

// General Functions (used in multiple files)

// General:			Function to be used later in the code
function microtime_float() {
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

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

function init_fields(&$header, &$footer, &$file_header, &$files_loop, &$file_footer, &$gloop, &$search_layout, &$gheader, &$gfooter) {
   $header = addslashes('[SEARCH]');
   
   $footer = addslashes('<tr><td colspan="5">&nbsp;</td></tr>
   <tr>
   <td colspan="5" class="mod_download_gallery_th_f">
   <table cellpadding="0" cellspacing="0" border="0" width="98%" style="display: [DISPLAY_PREVIOUS_NEXT_LINKS]">
   <tr>
   <td width="35%" align="left">[PREVIOUS_PAGE_LINK]</td>
   <td width="30%" align="center">[OF]</td>
   <td width="35%" align="right">[NEXT_PAGE_LINK]</td>
   </tr>
   </table>
   </td>
   </tr>
   </table>');
   
   $file_header = addslashes('<table cellpadding="0" cellspacing="0" border="0">
   <tr>
   <td class="mod_download_gallery_th_f"> [THTITLE] </td>
   <td class="mod_download_gallery_th_f"> [THCHANGED] </td>
   <td class="mod_download_gallery_th_f"> [THRELEASED] </td>
   <td class="mod_download_gallery_th_f"> [THSIZE] </td>
   <td class="mod_download_gallery_th_f"> [THCOUNT]  </td>
   </tr>');
   
   $files_loop = addslashes('<tr>
   <td class="mod_download_gallery_line_f"><img src="[FTIMAGE]" alt="" /> <a href="[LINK]" target="dlg"><b>[TITLE]</b></a></td>
   <td class="mod_download_gallery_line_rightalign_f"> [DATE]</td>
   <td class="mod_download_gallery_line_rightalign_f"> [RELEASED]</td>
   <td class="mod_download_gallery_line_rightalign_f"> [SIZE]</td>
   <td class="mod_download_gallery_line_rightalign_f"> [DL] </td>
   </tr>
   <tr>
   <td class="mod_download_gallery_line_text_f" colspan="5">[DESCRIPTION]</td>
   </tr>');
   
   $file_footer = '';
   $gheader = '';
   $gfooter = '';
   
   $gloop = addslashes('<tr>
   <td colspan="5">&nbsp;</td>
   </tr>
   <tr>
   <td class="mod_download_gallery_dgheader_f" colspan="5">[GROUPTITLE]</td>
   </tr>');
   
   $search_layout=addslashes('[SEARCHBOX] [SEARCHSUBMIT] [SEARCHRESULT]');
   return true;
}

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

?>