<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: fnProcessFile.php 2934 2011-07-29 21:01:14Z david.cole $
  Language:  PHP
  Date:      $Date: 2011-07-29 23:01:14 +0200 (ven., 29 juil. 2011) $
  Version:   $Revision: 2934 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

// To be able to access files in this CDash installation regardless
// of getcwd() value:
//
$cdashpath = dirname(dirname(__FILE__));
set_include_path($cdashpath . PATH_SEPARATOR . get_include_path());

require_once("cdash/common.php");
require_once("cdash/do_submit.php");


// Try to open the file and process it (call "do_submit" on it)
//
function ProcessFile($projectid, $filename)
{
  unset($fp);

  if(!file_exists($filename))
    {
    // check in parent dir also
    $filename = "../$filename";
    }

  if(file_exists($filename))
    {
    $fp = fopen($filename, 'r');
    }

  if($fp)
    {
    global $PHP_ERROR_SUBMISSION_ID;
    do_submit($fp, $projectid, '', false, $PHP_ERROR_SUBMISSION_ID);
    $PHP_ERROR_SUBMISSION_ID = 0;

    fclose($fp);
    // delete the temporary backup file since we now have a better-named one
    unlink($filename);
    $new_status = 2; // done, did call do_submit, finished normally
    }
  else
    {
    add_log("Cannot open file '".$filename."'", "ProcessFile",
      LOG_ERR, $projectid);
    $new_status = 3; // done, did *NOT* call do_submit
    }

  return $new_status;
}


?>
