<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: viewNotes.php 1398 2009-02-03 21:16:20Z jjomier $
  Language:  PHP
  Date:      $Date: 2009-02-03 22:16:20 +0100 (mar., 03 févr. 2009) $
  Version:   $Revision: 1398 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
$noforcelogin = 1;
include("cdash/config.php");
require_once("cdash/pdo.php");
include('login.php');
include_once("cdash/common.php");
include("cdash/version.php");

@$buildid = $_GET["buildid"];
@$date = $_GET["date"];

// Checks
if(!isset($buildid) || !is_numeric($buildid))
  {
  echo "Not a valid buildid!";
  return;
  }
 
$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);
  
$build_array = pdo_fetch_array(pdo_query("SELECT projectid FROM build WHERE id='$buildid'"));  
$projectid = $build_array["projectid"];
checkUserPolicy(@$_SESSION['cdash']['loginid'],$projectid);

if(!isset($date) || strlen($date)==0)
{ 
  $currenttime = time();
}
else
{
  $currenttime = mktime("23","59","0",date2month($date),date2day($date),date2year($date));
}
    
$project = pdo_query("SELECT * FROM project WHERE id='$projectid'");
if(pdo_num_rows($project)>0)
{
  $project_array = pdo_fetch_array($project);  
  $projectname = $project_array["name"];  
}

$previousdate = date(FMT_DATE,$currenttime-24*3600); 
$nextdate = date(FMT_DATE,$currenttime+24*3600);

$xml = '<?xml version="1.0"?><cdash>';
$xml .= "<title>CDash : ".$projectname."</title>";
$xml .= "<cssfile>".$CDASH_CSS_FILE."</cssfile>";
$xml .= "<version>".$CDASH_VERSION."</version>";

$xml .= get_cdash_dashboard_xml(get_project_name($projectid),$date);
  
// Build
$xml .= "<build>";
$build = pdo_query("SELECT * FROM build WHERE id='$buildid'");
$build_array = pdo_fetch_array($build); 
$siteid = $build_array["siteid"];
$site_array = pdo_fetch_array(pdo_query("SELECT name FROM site WHERE id='$siteid'"));
$xml .= add_XML_value("site",$site_array["name"]);
$xml .= add_XML_value("buildname",$build_array["name"]);
$xml .= add_XML_value("buildid",$build_array["id"]);
$xml .= "</build>";
  
  
$build2note = pdo_query("SELECT noteid,time FROM build2note WHERE buildid='$buildid'");
while($build2note_array = pdo_fetch_array($build2note))
  {
  $noteid = $build2note_array["noteid"];
  $note_array = pdo_fetch_array(pdo_query("SELECT * FROM note WHERE id='$noteid'"));
  $xml .= "<note>";
  $xml .= add_XML_value("name",$note_array["name"]);
  $xml .= add_XML_value("text",$note_array["text"]);
  $xml .= add_XML_value("time",$build2note_array["time"]);
  $xml .= "</note>";
  $text = $note_array["text"];
  $name = $note_array["name"];
  }

$xml .= "</cdash>";

// Now doing the xslt transition
generate_XSLT($xml,"viewNotes");
?>
