<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: hasfile.php 3334 2013-07-17 20:01:03Z zack.galbreath $
  Language:  PHP
  Date:      $Date: 2013-07-17 16:01:03 -0400 (Wed, 17 Jul 2013) $
  Version:   $Revision: 3334 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

// To be able to access files in this CDash installation regardless
// of getcwd() value:
//
$cdashpath = str_replace('\\', '/', dirname(dirname(__FILE__)));
set_include_path($cdashpath . PATH_SEPARATOR . get_include_path());

require_once("cdash/common.php");
require_once("cdash/pdo.php");

$md5sums_get = isset($_GET['md5sums']) ? htmlspecialchars(pdo_real_escape_string($_GET['md5sums'])) : '';
if($md5sums_get == '')
  {
  echo "md5sum not specified";
  return;
  }

$md5sums = split('[|\\.:,;]+', $md5sums_get);

foreach($md5sums as $md5sum)
  {
  if($md5sum == '') continue;
  $md5sum = pdo_real_escape_string($md5sum);
  $result = pdo_query("SELECT id FROM filesum WHERE md5sum='$md5sum'");
  //we don't have this file, add it to the list to send
  if(pdo_num_rows($result) == 0)
    {
    echo $md5sum . "\n";
    }
  }
?>
