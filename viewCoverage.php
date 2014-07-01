<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: viewCoverage.php 3459 2014-04-25 13:57:43Z jjomier $
  Language:  PHP
  Date:      $Date: 2014-04-25 09:57:43 -0400 (Fri, 25 Apr 2014) $
  Version:   $Revision: 3459 $

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
include("models/coveragefile2user.php");
include("models/user.php");
require_once("filterdataFunctions.php");

set_time_limit(0);

@$buildid = $_GET["buildid"];
if ($buildid != NULL)
  {
  $buildid = pdo_real_escape_numeric($buildid);
  }

@$date = $_GET["date"];
if ($date != NULL)
  {
  $date = htmlspecialchars(pdo_real_escape_string($date));
  }

if(isset($_GET["value1"]) && strlen($_GET["value1"])>0)
  {
  $filtercount = $_GET["filtercount"];
  }
else
  {
  $filtercount = 0;
  }

// Checks
if(!isset($buildid) || !is_numeric($buildid))
  {
  echo "Not a valid buildid!";
  return;
  }

@$userid = $_SESSION['cdash']['loginid'];
if(!isset($userid))
  {
  $userid = 0;
  }

$build_array = pdo_fetch_array(pdo_query("SELECT starttime,projectid,siteid,type,name FROM build WHERE id='$buildid'"));
$projectid = $build_array["projectid"];

if(!isset($projectid) || $projectid==0 || !is_numeric($projectid))
  {
  echo "This project doesn't exist. Maybe it has been deleted.";
  exit();
  }

checkUserPolicy(@$_SESSION['cdash']['loginid'],$projectid);

$project = pdo_query("SELECT name,coveragethreshold,nightlytime,showcoveragecode,displaylabels    FROM project WHERE id='$projectid'");
if(pdo_num_rows($project) == 0)
  {
  echo "This project doesn't exist.";
  exit();
  }


function compute_percentcoverage($loctested, $locuntested)
{
  $loc = $loctested + $locuntested;

  if($loc>0)
    {
    $percentcoverage = round($loctested/$loc*100, 2);
    }
  else
    {
    $percentcoverage = 0;
    }

  return $percentcoverage;
}


$role=0;
$user2project = pdo_query("SELECT role FROM user2project WHERE userid='$userid' AND projectid='$projectid'");
if(pdo_num_rows($user2project)>0)
  {
  $user2project_array = pdo_fetch_array($user2project);
  $role = $user2project_array["role"];
  }

$project_array = pdo_fetch_array($project);
$projectname = $project_array["name"];

$projectshowcoveragecode = 1;
if(!$project_array["showcoveragecode"] && $role<2)
  {
  $projectshowcoveragecode = 0;
  }

$projectdisplaylabels = $project_array["displaylabels"];

$xml = begin_XML_for_XSLT();
$xml .= "<title>CDash : ".$projectname."</title>";
$xml .= get_cdash_dashboard_xml_by_name($projectname,$date);
$xml .= "<buildid>".$buildid."</buildid>";

$siteid = $build_array["siteid"];
$buildtype = $build_array["type"];
$buildname = $build_array["name"];
$starttime = $build_array["starttime"];

$xml .= "<menu>";
$xml .= add_XML_value("back","index.php?project=".urlencode($projectname)."&date=".get_dashboard_date_from_build_starttime($build_array["starttime"],$project_array["nightlytime"]));
$previousbuildid = get_previous_buildid($projectid,$siteid,$buildtype,$buildname,$starttime);
if($previousbuildid>0)
  {
  $xml .= add_XML_value("previous","viewCoverage.php?buildid=".$previousbuildid);
  }
else
  {
  $xml .= add_XML_value("noprevious","1");
  }
$xml .= add_XML_value("current","viewCoverage.php?buildid=".get_last_buildid($projectid,$siteid,$buildtype,$buildname,$starttime));
$nextbuildid = get_next_buildid($projectid,$siteid,$buildtype,$buildname,$starttime);
if($nextbuildid>0)
  {
  $xml .= add_XML_value("next","viewCoverage.php?buildid=".$nextbuildid);
  }
else
  {
  $xml .= add_XML_value("nonext","1");
  }
$xml .= "</menu>";

$xml .= add_XML_value("filtercount",$filtercount);
if($filtercount>0)
  {
  $xml .= add_XML_value("showfilters",1);
  }

  // coverage
  $xml .= "<coverage>";
  $coverage = pdo_query("SELECT * FROM coveragesummary WHERE buildid='$buildid'");
  $coverage_array = pdo_fetch_array($coverage);
  $xml .= add_XML_value("starttime",date("l, F d Y",strtotime($build_array["starttime"])));
  $xml .= add_XML_value("loctested",$coverage_array["loctested"]);
  $xml .= add_XML_value("locuntested",$coverage_array["locuntested"]);

  $percentcoverage = compute_percentcoverage(
    $coverage_array["loctested"], $coverage_array["locuntested"]);

  $xml .= add_XML_value("loc",$coverage_array["loctested"]+$coverage_array["locuntested"]);
  $xml .= add_XML_value("percentcoverage",$percentcoverage);
  $xml .= add_XML_value("percentagegreen",$project_array["coveragethreshold"]);
  // Above this number of the coverage is green
  $metricpass = $project_array["coveragethreshold"]/100;
  $xml .= add_XML_value("metricpass",$metricpass);
  // Below this number of the coverage is red
  $metricerror = 0.7*($project_array["coveragethreshold"]/100);
  $xml .= add_XML_value("metricerror",$metricerror);


  // Only execute the label-related queries if labels are being
  // displayed:
  //
  if ($projectdisplaylabels)
    {
    // Get the set of labels involved:
    //
    $labels = array();

    $covlabels = pdo_all_rows_query(
      "SELECT DISTINCT id, text FROM label, label2coveragefile WHERE ".
      "label.id=label2coveragefile.labelid AND ".
      "label2coveragefile.buildid=".qnum($buildid));
    foreach($covlabels as $row)
      {
      $labels[$row['id']] = $row['text'];
      }

    // For each label, compute the percentcoverage for files with
    // that label:
    //
    if (count($labels) > 0)
      {
      $xml .= "<labels>";

      foreach ($labels as $id => $label)
        {
        $row = pdo_single_row_query(
          "SELECT COUNT(*) AS c, SUM(loctested) AS loctested, SUM(locuntested) AS locuntested ".
          "FROM label2coveragefile, coverage WHERE ".
          "label2coveragefile.labelid=".qnum($id)." AND ".
          "label2coveragefile.buildid=".qnum($buildid)." AND ".
          "coverage.buildid=label2coveragefile.buildid AND ".
          "coverage.fileid=label2coveragefile.coveragefileid");

        $loctested = $row['loctested'];
        $locuntested = $row['locuntested'];
        $percentcoverage = compute_percentcoverage($loctested, $locuntested);

        $xml .= "<label>";
        $xml .= add_XML_value("name", $label);
        $xml .= add_XML_value("percentcoverage", $percentcoverage);
        $xml .= "</label>";
        }

      $xml .= "</labels>";
      }
    }


  $coveredfiles = pdo_query("SELECT count(covered) FROM coverage WHERE buildid='$buildid' AND covered='1'");
  $coveredfiles_array = pdo_fetch_array($coveredfiles);
  $ncoveredfiles = $coveredfiles_array[0];

  $files = pdo_query("SELECT count(covered) FROM coverage WHERE buildid='$buildid'");
  $files_array = pdo_fetch_array($files);
  $nfiles = $files_array[0];

  $xml .= add_XML_value("totalcovered",$ncoveredfiles);
  $xml .= add_XML_value("totalfiles",$nfiles);
  $xml .= add_XML_value("buildid",$buildid);
  $xml .= add_XML_value("userid",$userid);


  $xml .= add_XML_value("showcoveragecode",$projectshowcoveragecode);
  $xml .= add_XML_value("displaylabels",$projectdisplaylabels);

  $nsatisfactorycoveredfiles = 0;
  $coveragetype = "gcov"; // default coverage to avoid warning

  $t0 = time();

  // Coverage files
  $coveragefile = pdo_query("SELECT c.locuntested,c.loctested,
                                    c.branchstested,c.branchsuntested,c.functionstested,c.functionsuntested,
                                    cf.fullpath
                            FROM coverage AS c, coveragefile AS cf 
                            WHERE c.buildid='$buildid' AND c.covered=1 AND c.fileid=cf.id");


  $directories = array();
  $covfile_array = array();
  while($coveragefile_array = pdo_fetch_array($coveragefile))
    {
    $covfile["covered"] = 1;

    // Compute the coverage metric for bullseye
    if($coveragefile_array["branchstested"]>0 || $coveragefile_array["branchsuntested"]>0 || $coveragefile_array["functionstested"]>0 || $coveragefile_array["functionsuntested"]>0)
      {
      // Metric coverage
      $metric = 0;
      if($coveragefile_array["functionstested"]+$coveragefile_array["functionsuntested"]>0)
        {
        $metric += $coveragefile_array["functionstested"]/($coveragefile_array["functionstested"]+$coveragefile_array["functionsuntested"]);
        }
      if($coveragefile_array["branchstested"]+$coveragefile_array["branchsuntested"]>0)
        {
        $metric += $coveragefile_array["branchstested"]/($coveragefile_array["branchstested"]+$coveragefile_array["branchsuntested"]);
        $metric /= 2.0;
        }

      $covfile["percentcoverage"] = sprintf("%3.2f",$metric*100);
      $covfile["coveragemetric"] = $metric;
      $coveragetype = "bullseye";
      }
    else // coverage metric for gcov
      {
      $covfile["coveragemetric"] = ($coveragefile_array["loctested"]+10)/($coveragefile_array["loctested"]+$coveragefile_array["locuntested"]+10);
      $coveragetype = "gcov";
      $covfile["percentcoverage"] = sprintf("%3.2f",$coveragefile_array["loctested"]/($coveragefile_array["loctested"]+$coveragefile_array["locuntested"])*100);
      }

    // Add the number of satisfactory covered files
    if($covfile["coveragemetric"]>=$metricpass)
      {
      $nsatisfactorycoveredfiles++;
      }
    
    // Store the directories path only for non-complete (100% coverage) files
    if($covfile["coveragemetric"] != 1.0)
      {
      $fullpath = $coveragefile_array["fullpath"];
      if(substr($fullpath,0,2)=="./")
        {
        $fullpath = substr($fullpath,2);  
        }
      $fullpath = dirname($fullpath);
      $directories[$fullpath]=1;
      }
    $covfile_array[] = $covfile;
    }

  // Add the coverage type
  $xml .= add_XML_value("coveragetype",$coveragetype);
  if(isset($_GET['status']))
    {
    $xml .= add_XML_value("status",$_GET['status']);
    }
  else
    {
    $xml .= add_XML_value("status",-1);
    }
  if(isset($_GET['dir']))
    {
    $xml .= add_XML_value("dir",$_GET['dir']);
    }
    
  $xml .= add_XML_value("totalsatisfactorilycovered",$nsatisfactorycoveredfiles);
  $xml .= add_XML_value("totalunsatisfactorilycovered",$nfiles-$nsatisfactorycoveredfiles);

  $xml .= "</coverage>";

  // Add the untested files
  $coveragefile = pdo_query("SELECT c.buildid FROM coverage AS c
                             WHERE c.buildid='$buildid' AND c.covered=0");
  while($coveragefile_array = pdo_fetch_array($coveragefile))
    {
    $covfile["covered"] = 0;
    $covfile["coveragemetric"] = 0;
    $covfile_array[] = $covfile;
    }

  $ncoveragefiles = array();
  $ncoveragefiles[0] = count($directories);
  $ncoveragefiles[1] = 0;
  $ncoveragefiles[2] = 0;
  $ncoveragefiles[3] = 0;
  $ncoveragefiles[4] = 0;
  $ncoveragefiles[5] = 0;
  $ncoveragefiles[6] = 0;
  $ncoveragefiles[7] = 0;
    
  foreach($covfile_array as $covfile)
    {
    if($covfile["covered"]==0)
      {
      $ncoveragefiles[1]++; // no coverage
      }
    else if($covfile["covered"]==1 && $covfile["percentcoverage"] == 0 )
      {
      $ncoveragefiles[2]++; // zero
      }
    else if($covfile["covered"]==1 && $covfile["coveragemetric"] < $metricerror)
      {
      $ncoveragefiles[3]++; // low
      }
    else if($covfile["covered"]==1 && $covfile["coveragemetric"] == 1.0)
      {
      $ncoveragefiles[6]++; // complete
      }
    else if($covfile["covered"]==1 && $covfile["coveragemetric"] >= $metricpass)
      {
      $ncoveragefiles[5]++; // satisfactory
      }
    else
      {
      $ncoveragefiles[4]++; // medium
      }
    $ncoveragefiles[7]++; // all
    }

  // Show the number of files covered by status
  $xml .= "<coveragefilestatus>";
  $xml .= add_XML_value("directories",$ncoveragefiles[0]);
  $xml .= add_XML_value("no",$ncoveragefiles[1]);
  $xml .= add_XML_value("zero",$ncoveragefiles[2]);
  $xml .= add_XML_value("low",$ncoveragefiles[3]);
  $xml .= add_XML_value("medium",$ncoveragefiles[4]);
  $xml .= add_XML_value("satisfactory",$ncoveragefiles[5]);
  $xml .= add_XML_value("complete",$ncoveragefiles[6]);
  $xml .= add_XML_value("all",$ncoveragefiles[7]);
  $xml .= "</coveragefilestatus>";

  // Filters:
  //
  // On this page, we don't need the 'sql' or its friend 'limit' from
  // the filterdata, since the actual sql query is deferred until
  // ajax/getviewcoverage.php (called by cdashViewCoverage.js).
  //
  $filterdata = get_filterdata_from_request();
  $xml .= $filterdata['xml'];
  $xml .= "</cdash>";
// Now doing the xslt transition
generate_XSLT($xml,"viewCoverage");
?>
