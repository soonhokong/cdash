<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: api_coverage.php 2618 2010-08-04 19:28:36Z david.cole $
  Language:  PHP
  Date:      $Date: 2010-08-04 21:28:36 +0200 (mer., 04 août 2010) $
  Version:   $Revision: 2618 $

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

// Return a tree of coverage directory with the number of line covered
// and not covered
include_once('api.php');

class CoverageAPI extends CDashAPI
{
  
  /** Return the coverage per directory */
  private function CoveragePerDirectory()
    {
    include_once('../cdash/common.php');  
    if(!isset($this->Parameters['project']))  
      {
      echo "Project not set";
      return;
      }
      
    $projectid = get_project_id($this->Parameters['project']);
    if(!is_numeric($projectid))
      {
      echo "Project not found";
      return;
      }
    
    // Select the last build that has coverage from the project  
    $query = pdo_query("SELECT buildid FROM coveragesummary,build WHERE build.id=coveragesummary.buildid
                              AND build.projectid='$projectid' ORDER BY buildid DESC LIMIT 1"); 
    echo pdo_error();
    
    if(pdo_num_rows($query) == 0)
      {
      echo "No coverage entries found for this project";
      return;
      }
    $query_array = pdo_fetch_array($query);
    $buildid = $query_array['buildid']; 
    
    // Find the coverage files
    $query = pdo_query("SELECT cf.fullpath,c.loctested,c.locuntested FROM coverage as c,coveragefile as cf
                 WHERE c.fileid=cf.id AND c.buildid='".$buildid."' ORDER BY cf.fullpath ASC"); 
    echo pdo_error();
    $coveragearray = array();
    while($query_array = pdo_fetch_array($query))
      {
      $fullpath = $query_array['fullpath'];
      $paths = explode('/',$fullpath);
      $current = array();
      for($i=1;$i<count($paths)-1;$i++)
        {  
        if($i==1)
          {  
          if(!isset($coveragearray[$paths[$i]]))
            {
            $coveragearray[$paths[$i]] = array();
            }
          $current = &$coveragearray[$paths[$i]];
          }
        else
          {
    
          if($i==count($paths)-2)
            { 
            if(isset($current[$paths[$i]]))
              {
              $v = $current[$paths[$i]]['locuntested'];
              $current[$paths[$i]]['locuntested'] = (integer)$v+$query_array['locuntested'];
              $v = $current[$paths[$i]]['loctested'];
              $current[$paths[$i]]['loctested'] = (integer)$v+$query_array['loctested'];
              
              }
            else
              {
              @$current[$paths[$i]]['locuntested'] = $query_array['locuntested'];  
              @$current[$paths[$i]]['loctested'] = $query_array['loctested']; 
              } 
            unset($current);
            }
          else
            {
            $current[$paths[$i]] = array();
            $current[$paths[$i]]['locuntested'] = 0;
            $current[$paths[$i]]['loctested'] = 0;
            $current = &$current[$paths[$i]];  
            }
          }  
        }
      }
    return $coveragearray;
    } // end function CoveragePerDirectory
  
  /** Run function */
  function Run()
    {
    switch($this->Parameters['task'])
      {
      case 'directory': return $this->CoveragePerDirectory();
      }
    } 
}

?>
