<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: getbuildid.php 3334 2013-07-17 20:01:03Z zack.galbreath $
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

@$project = $_GET['project'];
@$site = $_GET['site'];
@$siteid = $_GET['siteid'];
@$stamp = $_GET['stamp'];
@$name = $_GET['name'];

$project = htmlspecialchars(pdo_real_escape_string($project));
$site = htmlspecialchars(pdo_real_escape_string($site));
$siteid = pdo_real_escape_numeric($siteid);
$stamp = htmlspecialchars(pdo_real_escape_string($stamp));
$name = htmlspecialchars(pdo_real_escape_string($name));

$projectid = get_project_id($project);

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "<buildid>";

if(!is_numeric($projectid))
  {
  echo "not found</buildid>";
  return;
  }

if(!isset($siteid))
  {
  $sitequery = pdo_query("SELECT id FROM site WHERE name='$site'");
  if(pdo_num_rows($sitequery)>0)
    {
    $site_array = pdo_fetch_array($sitequery);
    $siteid = $site_array['id'];
    }
  }

if(!is_numeric($siteid))
  {
  echo "wrong site</buildid>";
  return;
  }  
                           
$buildquery = pdo_query("SELECT id FROM build WHERE siteid='$siteid' AND projectid='$projectid'
                         AND name='$name' AND stamp='$stamp'");
                                          
if(pdo_num_rows($buildquery)>0)
  {
  $buildarray = pdo_fetch_array($buildquery);
  $buildid = $buildarray['id'];
  echo $buildid."</buildid>";
  return;
  }

echo "not found</buildid>";
?>
