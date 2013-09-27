<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: testSummary.php 3191 2012-02-13 12:30:30Z jjomier $
  Language:  PHP
  Date:      $Date: 2012-02-13 13:30:30 +0100 (lun., 13 févr. 2012) $
  Version:   $Revision: 3191 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

/*
* testSummary.php displays a list of all builds that performed a given test
* on a specific day.  It also displays information (success, execution time)
* about each copy of the test that was run.
*/
$noforcelogin = 1;
include("cdash/config.php");
require_once("cdash/pdo.php");
include('login.php');
include_once("cdash/common.php");
include_once("cdash/repository.php");
include("cdash/version.php");

$date = $_GET["date"];
if(!isset($date) || strlen($date)==0)
  {
  die('Error: no date supplied in query string');
  }
$projectid = $_GET["project"];
if(!isset($projectid))
  {
  die('Error: no project supplied in query string');
  }
// Checks
if(!isset($projectid) || !is_numeric($projectid))
  {
  echo "Not a valid projectid!";
  return;
  }

$testName = $_GET["name"];
if(!isset($testName))
  {
  die('Error: no test name supplied in query string');
  }

$start = microtime_float();

$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);
$project = pdo_query("SELECT * FROM project WHERE id='$projectid'");
if(pdo_num_rows($project)>0)
  {
  $project_array = pdo_fetch_array($project);
  $projectname = $project_array["name"];
  $nightlytime = $project_array["nightlytime"];
  }

checkUserPolicy(@$_SESSION['cdash']['loginid'],$project_array["id"]);

$xml = '<?xml version="1.0" encoding="utf-8"?><cdash>';
$xml .= "<title>CDash : ".$projectname."</title>";
$xml .= "<cssfile>".$CDASH_CSS_FILE."</cssfile>";
$xml .= "<version>".$CDASH_VERSION."</version>";

$xml .= get_cdash_dashboard_xml_by_name($projectname,$date);
$xml .= add_XML_value("testName",$testName);

$xml .= "<menu>";
list ($previousdate, $currentstarttime, $nextdate,$today) = get_dates($date,$nightlytime);
$xml .= add_XML_value("back","index.php?project=".urlencode($projectname)."&date=".$date);
$xml .= add_XML_value("previous", "testSummary.php?project=$projectid&name=$testName&date=$previousdate");
$xml .= add_XML_value("current", "testSummary.php?project=$projectid&name=$testName&date=".date(FMT_DATE));
if($date!="" && date(FMT_DATE, $currentstarttime)!=date(FMT_DATE))
  {
  $xml .= add_XML_value("next","testSummary.php?project=$projectid&name=$testName&date=$nextdate");
  }
else
  {
  $xml .= add_XML_value("nonext","1");
  }
$xml .= "</menu>";

//get information about all the builds for the given date and project
$xml .= "<builds>\n";

$testName = pdo_real_escape_string($testName);
list ($previousdate, $currentstarttime, $nextdate) = get_dates($date,$project_array["nightlytime"]);
$beginning_timestamp = $currentstarttime;
$end_timestamp = $currentstarttime+3600*24;

$beginning_UTCDate = gmdate(FMT_DATETIME,$beginning_timestamp);
$end_UTCDate = gmdate(FMT_DATETIME,$end_timestamp);

// Add the date/time
$xml .= add_XML_value("projectid",$projectid);
$xml .= add_XML_value("currentstarttime",$currentstarttime);
$xml .= add_XML_value("teststarttime",date(FMT_DATETIME,$beginning_timestamp));
$xml .= add_XML_value("testendtime",date(FMT_DATETIME,$end_timestamp));

$query = "SELECT build.id,build.name,build.stamp,build2test.status,build2test.time,build2test.testid AS testid,site.name AS sitename
          FROM build
          JOIN build2test ON (build.id = build2test.buildid)
          JOIN site ON (build.siteid = site.id)
          WHERE build.projectid = '$projectid'
          AND build.starttime>='$beginning_UTCDate'
          AND build.starttime<'$end_UTCDate'
          AND build2test.testid IN (SELECT id FROM test WHERE name='$testName')
          ORDER BY build2test.status";

$result = pdo_query($query);

//now that we have the data we need, generate some XML
while($row = pdo_fetch_array($result))
  {
  $buildid = $row["id"];
  $xml .= "<build>\n";

  // Find the repository revision
  $xml .= "<update>";
  // Return the status
  $status_array = pdo_fetch_array(pdo_query("SELECT status,revision,priorrevision,path
                                              FROM buildupdate,build2update AS b2u
                                              WHERE b2u.updateid=buildupdate.id
                                              AND b2u.buildid='$buildid'"));
  if(strlen($status_array["status"]) > 0 && $status_array["status"]!="0")
    {
    $xml .= add_XML_value("status",$status_array["status"]);
    }
  else
    {
    $xml .= add_XML_value("status",""); // empty status
    }
  $xml .= add_XML_value("revision",$status_array["revision"]);
  $xml .= add_XML_value("priorrevision",$status_array["priorrevision"]);
  $xml .= add_XML_value("path",$status_array["path"]);
  $xml .= add_XML_value("revisionurl",
          get_revision_url($projectid, $status_array["revision"], $status_array["priorrevision"]));
  $xml .= add_XML_value("revisiondiff",
          get_revision_url($projectid, $status_array["priorrevision"], '')); // no prior prior revision...
  $xml .= "</update>";

  $xml .= add_XML_value("site", $row["sitename"]);
  $xml .= add_XML_value("buildName", $row["name"]);
  $xml .= add_XML_value("buildStamp", $row["stamp"]);
  $xml .= add_XML_value("time", $row["time"]);

//$xml .= add_XML_value("details", $row["details"]) . "\n";

  $buildLink = "viewTest.php?buildid=$buildid";
  $xml .= add_XML_value("buildLink", $buildLink);
  $testid = $row["testid"];
  $testLink = "testDetails.php?test=$testid&build=$buildid";
  $xml .= add_XML_value("testLink", $testLink);
  switch($row["status"])
    {
    case "passed":
      $xml .= add_XML_value("status", "Passed");
      $xml .= add_XML_value("statusclass", "normal");
      break;
    case "failed":
      $xml .= add_XML_value("status", "Failed");
      $xml .= add_XML_value("statusclass", "error");
      break;
    case "notrun":
      $xml .= add_XML_value("status", "Not Run");
   $xml .= add_XML_value("statusclass", "warning");
      break;
    }
  $xml .= "</build>\n";
  }
$xml .= "</builds>\n";

$end = microtime_float();
$xml .= "<generationtime>".round($end-$start,3)."</generationtime>";
$xml .= "</cdash>\n";

// Now doing the xslt transition
generate_XSLT($xml,"testSummary");
?>
