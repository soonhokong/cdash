<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: processfile.php 2934 2011-07-29 21:01:14Z david.cole $
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
require_once("cdash/fnProcessFile.php");
require_once("cdash/pdo.php");

ob_start();
set_time_limit(0);
ignore_user_abort(TRUE);

@$projectid = $_REQUEST['projectid'];
@$filename = $_REQUEST['filename'];
@$callit = $_REQUEST['callit'];

if (!is_numeric($projectid) || $projectid == 0)
  {
  trigger_error(
    'processfile.php: projectid no good',
    E_USER_ERROR);
  }

if (!$filename)
  {
  trigger_error(
    'processfile.php: filename no good',
    E_USER_ERROR);
  }

if (!isset($callit))
  {
  $callit = 1;
  }

register_shutdown_function('PHPErrorHandler', $projectid);

echo "<pre>\n";

if ($callit)
  {
  echo "before ProcessFile call\n";
  $status = ProcessFile($projectid, $filename);
  echo "after ProcessFile call\n";
  echo "status = $status\n";
  }
else
  {
  echo "no ProcessFile call\n";
  echo "callit = $callit\n";
  }

echo "</pre>\n";

ob_end_flush();

?>
