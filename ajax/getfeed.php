<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: getfeed.php 3434 2014-01-23 11:53:06Z jjomier $
  Language:  PHP
  Date:      $Date: 2014-01-23 06:53:06 -0500 (Thu, 23 Jan 2014) $
  Version:   $Revision: 3434 $

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

include("cdash/config.php");
require_once("cdash/pdo.php");
include_once("cdash/common.php");
include("cdash/version.php");
$noforcelogin = 1;
include('login.php');
include("models/feed.php");

$projectid = pdo_real_escape_numeric($_GET["projectid"]);
if(!isset($projectid) || !is_numeric($projectid))
  {
  die("Not a valid projectid!");
  return;
  }

$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);

$feed = new Feed();
checkUserPolicy(@$_SESSION['cdash']['loginid'],$projectid);

// Return when the feed was seen
function get_elapsed_time($date)
  {
  $lastpingtime = '';
  $diff = time()-strtotime($date." UTC");
  $days = $diff/(3600*24);
  if(floor($days)>0)
    {
    $lastpingtime .= floor($days)." days ";
    $diff = $diff-(floor($days)*3600*24);
    return $lastpingtime;
    }
  $hours = $diff/(3600);
  if(floor($hours)>0)
    {
    $lastpingtime .= floor($hours)." hours ";
    $diff = $diff-(floor($hours)*3600);
    return $lastpingtime;
    }
  $minutes = $diff/(60);
  if($minutes>0)
    {
    $lastpingtime .= floor($minutes)." minutes";
    }

  return $lastpingtime;
  } // end function

// Returns the feed type
function get_feed_type($type)
  {
  switch($type)
    {
    case Feed::TypeUnknown: return "NA";
    case Feed::TypeUpdate: return "UPDATE";
    case Feed::TypeBuildError: return "BUILD ERROR";
    case Feed::TypeBuildWarning: return "BUILD WARNING";
    case Feed::TypeTestPassing: return "TEST PASSING";
    case Feed::TypeTestFailing: return "TEST FAILING";
    }
  return "NA";
  }
// Returns the feed link
function get_feed_link($type,$buildid,$description)
  {
  if($type == Feed::TypeUpdate)
    {
    return '<a href="viewUpdate.php?buildid='.$buildid.'">'.$description.'</a>';
    }
  else if($type == Feed::TypeBuildError)
    {
    return '<a href="viewBuildError.php?buildid='.$buildid.'">'.$description.'</a>';;
    }
  else if($type == Feed::TypeBuildWarning)
    {
    return '<a href="viewBuildError.php?type=1&buildid='.$buildid.'">'.$description.'</a>';
    }
  else if($type == Feed::TypeTestPassing)
    {
    return '<a href="viewTest.php?onlypassed&buildid='.$buildid.'">'.$description.'</a>';
    }
  else if($type == Feed::TypeTestFailing)
    {
    return '<a href="viewTest.php?onlyfailed&buildid='.$buildid.'">'.$description.'</a>';
    }
  else if($type == Feed::TypeTestNotRun)
    {
    return '<a href="viewTest.php?onlynotrun&buildid='.$buildid.'">'.$description.'</a>';
    }
  return "";
  }

$feeds = $feed->GetFeed($projectid,5); // display the last five submissions
foreach($feeds as $f)
  {
  ?>
 <?php
   $elapsedtime = get_elapsed_time($f["date"]);
   if($elapsedtime == '')
     {
     $elapsedtime = 'Some time';
     }
   if($elapsedtime == '0m')
     {
     echo "Just now: ";
     }
   else
     {
     echo "<b>".$elapsedtime." ago: </b>";
     }
    ?>
 <?php //echo get_feed_type($f["type"]) ?>
<?php echo get_feed_link($f["type"],$f["buildid"],$f["description"]); ?>
<br/>
<?php  } // End looping through feed ?>
<?php if(count($feeds)>0) { ?>
<div id="feedmore"><a href="viewFeed.php?projectid=<?php echo $projectid; ?>">See full feed</a></div>
<?php } ?>


