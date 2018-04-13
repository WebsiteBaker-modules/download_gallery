<?php

/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of dlc.php
 *
 * @package      Addon_download_gallery
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.62
 * @revision     $Id: $
 * @since        File available since 21.09.2017
 * @deprecated   no / since 0000/00/00
 * @description  dlc.php delivers the selected file, if the user has permissions,
 *               and increments the download counter. For more about, see info.php
 *               Works with WB from 2.11.x and up
 *
 *               How to use:   dlc.php?id=1234567890&file=0123456789abcdef
 *               Where 'file' is the ID-Key representing the record-id of the requested file
 *               and 'id' is the timestamp of last modification of this record.
 *               An record is valid only, if record-id AND modified_when matches the request
 */


// include needed script files
    require dirname(dirname(__DIR__)).'/config.php';
//    if (!class_exists('SecureTokens')) {
//        require WB_PATH.'/framework/SecureTokens.php';
//    }
    require WB_PATH.'/framework/functions.php';
        
// validate arguments
    $iProve  = intval(isset($_GET['id']) ? $_GET['id'] : 0);
    if(!isset($_GET['file']) || !is_numeric($_GET['file'])) {
        header('Location: ../index.php');
    } else {
        $iFileId = $_GET['file'];
    }
    
    if (!$iProve || !$iFileId) {
        $sErrMsg = 'invalid arguments';
        goto REQUEST_ERR;
    }
    
// search for and test if file is available and readable
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'mod_download_gallery_files` '
         . 'WHERE `file_id`='.$iFileId.' AND `modified_when`='.$iProve.' ';
    $oFile = $database->query($sql);
    $aDlgFile = $oFile->fetchRow(MYSQLI_ASSOC);
    $sDownloadFile = WB_PATH.$aDlgFile['link'];

    if (!is_readable( $sDownloadFile )) {
        $sErrMsg = 'requested file not available';
        goto REQUEST_ERR;
    }
    $iDlFileSize = filesize($sDownloadFile);
// search for the corresponding page record
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'pages` '
         . 'WHERE `page_id`='.$aDlgFile['page_id'];
    if (!(($oPage = $database->query($sql)) && ($aPage = $oPage->fetchRow(MYSQLI_ASSOC)))) {
        $sErrMsg = 'corresponding page not available';
        goto REQUEST_ERR;
    }
// check if current user has rights to download this file
    if (!in_array($aPage['visibility'], ['public','hidden'])) {
        $iUserId = intval(isset($_SESSION['USER_ID']) ? $_SESSION['USER_ID'] : 0);
        if ($iUserId && in_array($aPage['visibility'], ['registered','private'])) {
//--- Callback returns true if intersection of both lists is greater then 0 ------------//
            $cbGroupMatch = function($sGroup1, $sGroup2) {
                $aResult = array_intersect(
                    preg_split('/[,; |]/', $sGroup1, -1, PREG_SPLIT_NO_EMPTY),
                    preg_split('/[,; |]/', $sGroup2, -1, PREG_SPLIT_NO_EMPTY)
                );
                return (bool) sizeof($aResult);
            };
//--------------------------------------------------------------------------------------//
// check if current user has viewing rights to this page
            if (
                !$cbGroupMatch($_SESSION['GROUPS_ID'], $aPage['viewing_groups']) &&
                !$cbGroupMatch($iUserId, $aPage['viewing_users']) &&
                $iUserId != 1){
                unset($cbGroupMatch);
                $sErrMsg = 'access denied';
                goto REQUEST_ERR;
            }
            unset($cbGroupMatch);
        } else {
            $sErrMsg = 'access denied';
            goto REQUEST_ERR;
        }
    }
// increment download counter:
    $sql = 'UPDATE `'.TABLE_PREFIX.'mod_download_gallery_files` '
         . 'SET `dlcount`=`dlcount`+1 '
         . 'WHERE `file_id`='.$iFileId;
    if ($database->query($sql)) {
// clear output buffer
        while (ob_get_level()) { ob_end_clean(); }
      
// deliver the file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($aDlgFile['link']).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.$iDlFileSize);
        readfile(WB_PATH.$aDlgFile['link']);
        exit;
    }
    $sErrMsg = 'unable to modify download counter';
REQUEST_ERR:
// send error messages
    echo $sErrMsg;
    exit;