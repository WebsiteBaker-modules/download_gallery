<?php
/*
 * CMS module: Download Gallery 2
 * Copyright and more information see file info.php
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));

include_once(WB_PATH.'/modules/download_gallery/functions.php');

if(LANGUAGE_LOADED) {
	if(!file_exists(WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php')) {
		require_once(WB_PATH.'/modules/download_gallery/languages/EN.php');
	} else {
		require_once(WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php');
	}
}

// load frontend.css if the template forgot it:
if((!function_exists('register_frontend_modfiles') || !defined('MOD_FRONTEND_CSS_REGISTERED')) &&
     file_exists(WB_PATH .'/modules/download_gallery/frontend.css')) {
        echo '<style type="text/css">';
        include(WB_PATH .'/modules/download_gallery/frontend.css');
        echo "\n</style>\n";
}

// For the curious: How fast are we?
$time_start = microtime_float();
echo "<!-- start download gallery start -->\n";

// Get user's username, display name, email, and id - needed for insertion into download info
$users = array();
$query_users = $database->query("SELECT user_id,username,display_name,email FROM ".TABLE_PREFIX."users");
if($query_users->numRows() > 0) {
	while($user = $query_users->fetchRow()) {
		// Insert user info into users array
		$user_id = $user['user_id'];
		$users[$user_id]['username'] = $user['username'];
		$users[$user_id]['display_name'] = $user['display_name'];
		$users[$user_id]['email'] = $user['email'];
	}
}

// Get settings
$query_settings = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_settings WHERE section_id = '$section_id'");
if($query_settings->numRows() > 0) {
	$fetch_settings = $query_settings->fetchRow();
	$setting_header = $fetch_settings['header'];
	$setting_footer = $fetch_settings['footer'];
	$setting_file_header = $fetch_settings['file_header'];
	$setting_files_loop = $fetch_settings['files_loop'];
	$setting_file_footer = $fetch_settings['file_footer'];
	$setting_files_per_page = $fetch_settings['files_per_page'];
	$setting_fs_roundup = $fetch_settings['file_size_roundup'];
	$setting_fs_decimals = $fetch_settings['file_size_decimals'];
	$setting_ordering = $fetch_settings['ordering'];
	$setting_extordering = $fetch_settings['extordering'];
	$setting_userupload = $fetch_settings['userupload'];
	$setting_group = $fetch_settings['gloop'];
	$setting_groupheader = $fetch_settings['gheader'];
	$setting_groupfooter = $fetch_settings['gfooter'];
	$setting_search_filter = $fetch_settings['search_filter'];
	$setting_search_layout = $fetch_settings['search_layout'];
} else {
	$setting_header = '';
	$setting_files_loop = '';
	$setting_footer = '';
	$setting_files_per_page = '';
}

$orderkey=$fetch_settings['ordering'];
if($orderkey =='2' or $orderkey == '3') {
	$orderby=TABLE_PREFIX."mod_download_gallery_files.title";
} else {
	$orderby=TABLE_PREFIX."mod_download_gallery_files.position";
}

if ($orderkey == '0' or $orderkey == '2') {
	$ordering = "ASC";
} else {
	$ordering = "DESC";
}

// init vars:
$adtitle = '';
$adchanged = '';
$adreleased = '';
$adsize = '';
$adcount = '';
$ctitle = 'TA';
$cchanged = 'CA';
$creleased = 'RA';
$csize = 'SA';
$ccount = 'DA';
$checkfiles = false;
$position = 0;
$dlsearch="";
$searchfor="";
$search_num = 0;
$sort = "";

// begin of checking of user input

// is local search active?
if(isset($_POST['searchfor'])){
        $searchfor = htmlspecialchars($_POST['searchfor'], ENT_QUOTES);
}

// there may be up to 8 different buttons
// check which ne the user pressed, if any
// (we do not need to check the search button, this is done by the searchfor field)
if ((isset($_POST['NB']))  // go to next page
          AND (isset($_POST['NP'])) and (is_numeric($_POST['NP'])) AND ($_POST['NP'] >= 0)) {
	$position = (int) $_POST['NP']; // start point (number of first entry to display)
        if (isset($_POST['SF'])) {
                $sort = $_POST['SF'];
        }
} elseif ((isset($_POST['PB']))  // go to prev page
          AND (isset($_POST['PP'])) and (is_numeric($_POST['PP'])) AND ($_POST['PP'] >= 0)) {
	$position = (int) $_POST['PP']; // start point (number of first entry to display)
        if (isset($_POST['SF'])) {
                $sort = $_POST['SF'];
        }
}

if (isset($_POST['R1'])) {  // sort by title
  $sort = $_POST['sort1'];
} elseif (isset($_POST['R2'])) {  // sort by time stamp
  $sort = $_POST['sort2'];
} elseif (isset($_POST['R3'])) {  // sort by size
  $sort = $_POST['sort3'];
} elseif (isset($_POST['R4'])) {  // sort by number of downloads
  $sort = $_POST['sort4'];
} elseif (isset($_POST['R5'])) {  // sort by release date
  $sort = $_POST['sort5'];
} elseif (isset($_POST['ShowAll'])) { // clean search field
        $searchfor="";
        $position = 0;
} elseif ($searchfor=="") {  // no sort button was pressed and probably first call, 
  $checkfiles = true;        // so we check whether files have been updated
}

// check whether sorting is active:
if ($sort == 'TA') {
      $orderby=TABLE_PREFIX."mod_download_gallery_files.title";
      $ordering = "ASC";
      $adtitle = 'mod_download_gallery_asc_f';
      $ctitle = 'TD';
} elseif ($sort == 'TD') {
      $orderby=TABLE_PREFIX."mod_download_gallery_files.title";
      $ordering = "DESC";
      $adtitle = 'mod_download_gallery_desc_f';
} elseif ($sort == 'CA') {
      $orderby=TABLE_PREFIX."mod_download_gallery_files.modified_when";
      $ordering = "ASC";
      $adchanged = 'mod_download_gallery_asc_f';
      $cchanged = 'CD';
} elseif ($sort == 'CD') {
      $orderby=TABLE_PREFIX."mod_download_gallery_files.modified_when";
      $ordering = "DESC";
      $adchanged = 'mod_download_gallery_desc_f';

} elseif ($sort == 'RA') {
      $orderby=TABLE_PREFIX."mod_download_gallery_files.released";
      $ordering = "ASC";
      $adreleased = 'mod_download_gallery_asc_f';
      $creleased = 'RD';
} elseif ($sort == 'RD') {
      $orderby=TABLE_PREFIX."mod_download_gallery_files.released";
      $ordering = "DESC";
      $adreleased= 'mod_download_gallery_desc_f';

} elseif ($sort == 'SA') {
      $orderby = TABLE_PREFIX."mod_download_gallery_files.size";
      $ordering = "ASC";
      $adsize = 'mod_download_gallery_asc_f';
      $csize = 'SD';
} elseif ($sort == 'SD') {
      $orderby=TABLE_PREFIX."mod_download_gallery_files.size";
      $ordering = "DESC";
      $adsize = 'mod_download_gallery_desc_f';
} elseif ($sort == 'DA') {
      $orderby=TABLE_PREFIX."mod_download_gallery_files.dlcount";
      $ordering = "ASC";
      $adcount = 'mod_download_gallery_asc_f';
      $ccount = 'DD';
} elseif ($sort == 'DD') {
      $orderby=TABLE_PREFIX."mod_download_gallery_files.dlcount";
      $ordering = "DESC";
      $adcount = 'mod_download_gallery_desc_f';
} 
// end of checking of user input


// Get total number of available download entries
$query_total_num = $database->query("SELECT file_id FROM ".TABLE_PREFIX."mod_download_gallery_files WHERE section_id = '$section_id' AND active = '1' AND title != ''");
$total_num = $query_total_num->numRows();

// Work-out if we need to add limit code to sql
if($setting_files_per_page != 0) {
	$limit_sql = " LIMIT $position,$setting_files_per_page";
} else {
	$limit_sql = "";
}

//Query for serach results
if ($searchfor!="") {
	$dlsearch= " AND (".TABLE_PREFIX."mod_download_gallery_files.title LIKE '%$searchfor%'
			OR ".TABLE_PREFIX."mod_download_gallery_files.description LIKE '%$searchfor%')";
        $query_filter_num = $database->query("SELECT file_id FROM ".TABLE_PREFIX."mod_download_gallery_files WHERE section_id = '$section_id' AND active = '1' AND title != '' " .$dlsearch);
        $search_num = $query_filter_num->numRows();
}

// Query files (for this page)
$query_files = $database->query("SELECT file_id, ".TABLE_PREFIX."mod_download_gallery_files.title,link,description,modified_by,
		modified_when,filename,extension,dlcount,size,released,	".TABLE_PREFIX."mod_download_gallery_files.group_id
		FROM ".TABLE_PREFIX."mod_download_gallery_files
		LEFT JOIN ".TABLE_PREFIX."mod_download_gallery_groups
		ON (".TABLE_PREFIX."mod_download_gallery_files.group_id = ".TABLE_PREFIX."mod_download_gallery_groups.group_id)
		WHERE  ".TABLE_PREFIX."mod_download_gallery_files.section_id = '$section_id'
		AND  ".TABLE_PREFIX."mod_download_gallery_files.active = '1'
		AND  ".TABLE_PREFIX."mod_download_gallery_files.title != ''
		".$dlsearch."
		ORDER BY ".TABLE_PREFIX."mod_download_gallery_groups.position, $orderby $ordering " . $limit_sql);

$num_files = $query_files->numRows();

// build current link, should be secure against xss:
if ((isset($_SERVER['HTTPS'])) and ($_SERVER['HTTPS']=="on")) {
        $selflink = "https://";
} else {        
        $selflink = "http://";
}
$selflink .= $_SERVER['SERVER_NAME']. $_SERVER['SCRIPT_NAME'];

//replace search in heading with searchlayout replacing searchbox submit and reset
if($setting_search_filter == '1') {
        //create the search form
        $searchstart="<div class='dlsearch'>\n";
        $searchbox= "<input type='text' name='searchfor' value='$searchfor' />\n";
        $searchsubmit = '<input type="submit" value="'.$DGTEXT['SEARCHinLIST']."\" />\n";
        if ($searchfor!='') {
                $searchsubmit .= '<input type="submit" name="ShowAll" value="'.$DGTEXT['SHOW_ALL']."\" />\n";
        }
        if ($searchfor=='') {
                $searchresult= '<p>&nbsp;</p>';
        } else {
                $searchresult= str_replace(array('[SEARCHMATCH]', '[OUT_OF1]', '[OUT_OF2]', '[ITEMS]'),
                                   array($DGTEXT['SEARCHMATCH'], $DGTEXT['OUT_OF1'], $DGTEXT['OUT_OF2'], $DGTEXT['ITEMS']),
                                   "<p>[SEARCHMATCH] <b>$search_num</b> [OUT_OF1] <b>$searchfor</b> [OUT_OF2] <b>$total_num</b> [ITEMS]</p>");
        }
        $searchend="</div>\n";

	$search = $searchstart;
	$search .=str_replace(array('[SEARCHBOX]','[SEARCHSUBMIT]','[SEARCHRESULT]'), array($searchbox, $searchsubmit, $searchresult), $setting_search_layout);
	$search .=$searchend;
} else {
	$search=str_replace(array('[SEARCHBOX]','[SEARCHSUBMIT]','[SEARCHRESULT]'), array('', '', ''), $setting_search_layout);
}

// Create previous and next links
if($setting_files_per_page != 0) {
        if ($search_num == 0) {
                $view_num = $total_num;
        } else {
                $view_num = $search_num;
        }

	if($position > 0) {
                // I am not sure whether someone will need NEXT entry and PREVIOUS entry at all, but it was already here:
                $previous_link = '<input type="hidden" name="PP" value="'.($position-1).'" />
                <button class="mod_download_gallery_btn_f" type="submit" name="PB">&lt;&lt; '.$TEXT['PREVIOUS']."</button>\n";
                
                $previous_page_link = '<input type="hidden" name="PP" value="'.($position-$setting_files_per_page).'" />
                <button class="mod_download_gallery_btn_f" type="submit" name="PB">&lt;&lt; '.$TEXT['PREVIOUS_PAGE']."</button>\n";
	} else {
		$previous_link = '';
		$previous_page_link = '';
	}

	if($position+$setting_files_per_page >= $view_num) {
		$next_link = '';
		$next_page_link = '';
	} else {
                // I am not sure whether someone will need NEXT entry and PREVIOUS entry at all, but it was already here:
                $next_link = '<input type="hidden" name="NP" value="'.($position+1).'" />
                <button class="mod_download_gallery_btn_f" type="submit" name="NB">'.$TEXT['NEXT']." &gt;&gt;</button>\n";

                $next_page_link = '<input type="hidden" name="NP" value="'.($position+$setting_files_per_page).'" />
                <button class="mod_download_gallery_btn_f" type="submit" name="NB">'.$TEXT['NEXT_PAGE']." &gt;&gt;</button>\n";
	}

        // check whether less entries available than allowed
        if($position+$setting_files_per_page > $view_num) {
		$num_of = $view_num;
	} else {
		$num_of = $position+$setting_files_per_page;
	}
        
	$out_of = ($position+1).'-'.$num_of.' '.strtolower($TEXT['OUT_OF']).' '.$view_num;
	$of =     ($position+1).'-'.$num_of.' '.strtolower($TEXT['OF']).' '.$view_num;
	$display_previous_next_links = '';
} else {
	$display_previous_next_links = 'none';
}

// Print header
//echo "\n<!-- output first line here: -->\n";
echo "<form name='dlg_$section_id' method='post' action='$selflink'>\n";

if($display_previous_next_links == 'none') {
	echo  str_replace(array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]','[SEARCH]'),
                          array('','','','','','', $display_previous_next_links, $search), $setting_header);
} else {
	echo str_replace(array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]','[SEARCH]'),
                         array($next_page_link, $next_link, $previous_page_link, $previous_link, $out_of, $of, $display_previous_next_links, $search), $setting_header);
}

/*Display message if there is no search result
if (isset($_POST['searchfor'])and $num_files==0){
	echo '<br />';
	echo $DGTEXT['NOMATCH'] . ": $searchfor";
	echo '<br />';
	}*/

// Loop through and show downloads
$pregroup = '';
if($num_files > 0) {
	$counter = 0;

	// Add buttons, currently only in $setting_file_header:
	$setting_file_header = str_replace('[THTITLE]', '<input type="hidden" name="sort1" value="[CT]" />
            <button class="mod_download_gallery_btn_f [ADTITLE]" type="submit" name="R1" value="[THTITLE]">[THTITLE]</button>', $setting_file_header);
	$setting_file_header = str_replace('[THCHANGED]', '<input type="hidden" name="sort2" value="[CC]" />
            <button class="mod_download_gallery_btn_ra_f [ADCHANGED]" type="submit" name="R2" value="[THCHANGED]">[THCHANGED]</button>', $setting_file_header);
	
	$setting_file_header = str_replace('[THRELEASED]', '<input type="hidden" name="sort5" value="[CR]" />
            <button class="mod_download_gallery_btn_ra_f [ADRELEASED]" type="submit" name="R5" value="[THRELEASED]">[THRELEASED]</button>', $setting_file_header);
	
	$setting_file_header = str_replace('[THSIZE]', '<input type="hidden" name="sort3" value="[CS]" />
            <button class="mod_download_gallery_btn_ra_f [ADSIZE]" type="submit" name="R3" value="[THSIZE]">[THSIZE]</button>', $setting_file_header);
	$setting_file_header = str_replace('[THCOUNT]', '<input type="hidden" name="sort4" value="[CD]" />
            <button class="mod_download_gallery_btn_ra_f [ADCOUNT]" type="submit" name="R4" value="[THCOUNT]">[THCOUNT]</button>', $setting_file_header);
        if ($sort!="") {
                $setting_file_header .= "<input type='hidden' name='SF' value='$sort' />";
        }

	// Replace vars with values
	$vars = array('[THTITLE]', '[THCHANGED]', '[THRELEASED]', '[THSIZE]', '[THCOUNT]', '[link]', '[ADTITLE]', '[ADCHANGED]', '[ADRELEASED]', '[ADSIZE]', '[ADCOUNT]',
		      '[CT]', '[CC]', '[CR]', '[CS]', '[CD]');
	$values = array($DGTEXT['THTITLE'], $DGTEXT['THCHANGED'], $DGTEXT['THRELEASED'], $DGTEXT['THSIZE'], $DGTEXT['THCOUNT'], $selflink,
			$adtitle, $adchanged, $adreleased, $adsize, $adcount, $ctitle, $cchanged, $creleased, $csize, $ccount);
	echo str_replace($vars, $values, $setting_file_header);

	while($file = $query_files->fetchRow()) {

		//$setting_group
		if($file['group_id']!=$pregroup){
			$group_id=$file['group_id'];

			$query_groups = $database->query("SELECT group_id,title FROM ".TABLE_PREFIX."mod_download_gallery_groups WHERE section_id = '$section_id' and group_id='$group_id'");
			$groups=$query_groups->fetchRow();


			if($groups['title'] != "") {
				$group_title=$groups['title'];
			}else{
				$group_title=$DGTEXT['NOGROUP'];
				}

                        if ($group_title!='') {
                                $gvars = array('[GROUPTITLE]');
                                $gvalues=array($group_title);
                                echo $setting_groupheader;
                                echo str_replace($gvars, $gvalues, $setting_group);
                                echo $setting_groupfooter;
                        }
		}

		$uid = $file['modified_by']; 		// User who last modified the file

		// file information
		$ext = $file['extension'];
		if (strpos($file['link'], '\\')===0) {
			$filelink = $file['link'];
		} else {
			$filelink = WB_PATH.str_replace(WB_URL,'',$file['link']);
		}
		
		if ($checkfiles) {
			// Workout date and time of last modified file
			$file_date = "";
			$file_time = "";
			$filesize = 0;
			$unixtime = 0;
			$uri = $file['filename'];
                //echo "<!-- DEBUG file: $uri -->\n";
			if (strpos($uri, ':/') > 1) {
				// remote file with uri:
				//echo "<!-- DEBUG: remote file with uri -->\n";
				$treffer = 0;
				$fp = fopen( $uri, "rb" );
				if( $fp ) {
					//echo "<!-- DEBUG: fopen success -->\n";
					$MetaData = stream_get_meta_data( $fp );
					//print_r ($MetaData);
					foreach( $MetaData['wrapper_data'] as $response ) {
						//echo "<!-- DEBUG response: $response -->\n";
						// case: redirection
						if( substr( strtolower($response), 0, 10 ) == 'location: ' ) {
							$newUri = substr( $response, 10 );
							fclose( $fp );
							break;
						}
						// case: last-modified
						elseif( substr( strtolower($response), 0, 15 ) == 'last-modified: ' ) {
							$unixtime = strtotime( substr($response, 15) );
							$treffer ++;
							if ($treffer > 1) break;
						}
						// case: Content-Length:
						elseif( substr( strtolower($response), 0, 16 ) == 'content-length: ' ) {
							$filesize = substr($response, 16);
							$treffer ++;
							if ($treffer > 1) break;
						}
					}
					fclose( $fp );
				} //else echo "<!-- DEBUG could not open uri $uri -->\n";
			} else {
                //echo "<!-- DEBUG filelink: $filelink -->\n";
				$unixtime = filemtime($filelink);
				$filesize = filesize($filelink);
			}

			$size = hfs($filesize, $setting_fs_roundup, $setting_fs_decimals);
			$file_date = date(DATE_FORMAT, $unixtime);
			$file_time = date(TIME_FORMAT, $unixtime);

			// update file size in DB if necessary:
			if (($file['size']<>$filesize) AND ($filesize > 0)) {
				//echo "<!-- DEBUG file size probably changed -->\n";
				$file_id = $file['file_id'];
				$database->query("UPDATE ".TABLE_PREFIX."mod_download_gallery_files SET size = '$filesize' WHERE file_id = '$file_id'");
				if($database->is_error()) {
				  echo "<!-- DEBUG DB error: ".$database->get_error()." -->\n";
				}
			}

			// update last modified in DB if necessary:
			if (($file['modified_when']<>$unixtime) AND ($unixtime > 0)) {
				//echo "<!-- DEBUG last modified probably changed -->\n";
				$file_id = $file['file_id'];
				$database->query("UPDATE ".TABLE_PREFIX."mod_download_gallery_files SET modified_when = '$unixtime' WHERE file_id = '$file_id'");
				if($database->is_error()) {
				  echo "<!-- DEBUG DB error: ".$database->get_error()." -->\n";
				}
				$file['modified_when'] = $unixtime;
			} else {   // if $checkfiles == false:
			  $file_date = date(DATE_FORMAT, $file['modified_when']);
			  $file_time = date(TIME_FORMAT, $file['modified_when']);
		  }


		} else {   // if $checkfiles == false:
			//echo "<!-- DEBUG checkfiles == false -->\n";
			$size = hfs($file['size'], $setting_fs_roundup, $setting_fs_decimals);
			$file_date = date(DATE_FORMAT, $file['modified_when']);
			$file_time = date(TIME_FORMAT, $file['modified_when']);
		}

		// Work-out the file link
		$file_link =  WB_URL . "/modules/download_gallery/dlc.php?file=" .$file['file_id']. "&amp;id=" .$file['modified_when']. "&amp;sid=$section_id";
		//Retrieve extension lists from database
		$query_fileext 	= $database->query("SELECT * FROM ".TABLE_PREFIX."mod_download_gallery_file_ext WHERE section_id = '$section_id'");
		$ft_image = "";
		if($query_fileext->numRows() > 0) {
			while($exts = $query_fileext->fetchRow()) {
				if (in_array($ext, explode(",",$exts['extensions']))) {
					$ft_image = WB_URL . '/modules/download_gallery/images/' . $exts['file_image'];
				}
			}
		}
		//echo "\nEXT: -$ext-  IMG: $ft_image\n";
		if ($ft_image == "") {
			$ft_image = WB_URL . '/modules/download_gallery/images/unknown.gif';
		}

		if($file['dlcount']=="") {
			$file['dlcount']="0";
		}
		
		$released = ($file['released'] > 0) ? (date('d.m.Y', $file['released'])) : '';
		
		// Replace vars with values
		$vars = array('[TITLE]', '[DESCRIPTION]', '[LINK]', '[EXT]', '[SIZE]', '[FTIMAGE]', '[DATE]', '[TIME]',
			      '[USER_ID]', '[USERNAME]', '[DISPLAY_NAME]', '[EMAIL]', '[DL]', '[FID]', '[RELEASED]');
		$dldescription=$file['description'];
		$wb->preprocess($dldescription);
		if(isset($users[$uid]['username']) AND $users[$uid]['username'] != '') {
			$values = array($file['title'],$dldescription , $file_link, $ext, $size, $ft_image, $file_date, $file_time,
					$uid, $users[$uid]['username'], $users[$uid]['display_name'], $users[$uid]['email'], $file['dlcount'],
					$file['file_id'], $released);
		} else {
			$values = array($file['title'], $dldescription, $file_link, $ext, $size, $ft_image, $file_date, $file_time,
					'', '', '', '', $file['dlcount'], $file['file_id'], $released);
		}
		echo str_replace($vars, $values, $setting_files_loop);
		// Increment counter
		$counter = $counter+1;
		$pregroup=$file['group_id'];
	}
	echo $setting_file_footer;
}

// Print footer
if($display_previous_next_links == 'none') {
	echo  str_replace(array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]','[SEARCH]'),
                          array('','','','','','', $display_previous_next_links,$search), $setting_footer);
} else {
	echo str_replace(array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]','[SEARCH]'),
                         array($next_page_link, $next_link, $previous_page_link, $previous_link, $out_of, $of, $display_previous_next_links,$search), $setting_footer);
}

//echo "\n<!-- output last line here: -->\n";
echo "</form>\n";

//echo $setting_userupload;
//display upload link if setting is set to allow this
if($setting_userupload==1 OR
	($setting_userupload==2
		AND isset($_SESSION['USER_ID'])
		AND $_SESSION['USER_ID'] != ""
		AND is_numeric($_SESSION['USER_ID'])
	)){
	echo '<a href="'.WB_URL.'/modules/download_gallery/dluser_add.php?sid='.$section_id.'&amp;pid='.$page_id.'">'.$DGTEXT['UPLOADFILE']. '</a>';
}

$time_end = microtime_float();
$runtime = round($time_end - $time_start, 4);
echo "<!-- gallery generated in ".$runtime." seconds -->\n";
echo "\n<!-- end download gallery -->\n";
?>