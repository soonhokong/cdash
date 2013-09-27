<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: api_project.php 3002 2011-10-06 22:20:55Z jcfr $
  Language:  PHP
  Date:      $Date: 2011-10-07 00:20:55 +0200 (ven., 07 oct. 2011) $
  Version:   $Revision: 3002 $

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

class ProjectAPI extends CDashAPI
{
  /** Return the list of all public projects */
  private function ListProjects()
    {
    include_once('../cdash/common.php');
    $query = pdo_query("SELECT id,name FROM project WHERE public=1 ORDER BY name ASC");
    while($query_array = pdo_fetch_array($query))
      {
      $project['id'] = $query_array['id'];
      $project['name'] = $query_array['name'];
      $projects[] = $project;
      }
    return $projects;
    } // end function ListProjects

  /**
   * Authenticate to the web API as a project admin
   * @param project the name of the project
   * @param key the web API key for that project
   */
  function Authenticate()
    {
    include_once('../cdash/common.php');
    if(!isset($this->Parameters['project']))
      {
      return array('status'=>false, 'message'=>"You must specify a project parameter.");
      }
    $projectid = get_project_id($this->Parameters['project']);
    if(!is_numeric($projectid) || $projectid <= 0)
      {
      return array('status'=>false, 'message'=>'Project not found.');
      }
    if(!isset($this->Parameters['key']) || $this->Parameters['key'] == '')
      {
      return array('status'=>false, 'message'=>"You must specify a key parameter.");
      }

    $key = $this->Parameters['key'];
    $query = pdo_query("SELECT webapikey FROM project WHERE id=$projectid");
    if(pdo_num_rows($query) == 0)
      {
      return array('status'=>false, 'message'=>"Invalid projectid.");
      }
    $row = pdo_fetch_array($query);
    $realKey = $row['webapikey'];

    if($key != $realKey)
      {
      return array('status'=>false, 'message'=>"Incorrect API key passed.");
      }
    $token = create_web_api_token($projectid);
    return array('status'=>true, 'token'=>$token);
    }

  /**
   * List all files for a given project
   * @param project the name of the project
   * @param key the web API key for that project
   * @param [match] regular expression that files must match
   * @param [mostrecent] include this if you only want the most recent match
   */
  function ListFiles()
    {
    include_once('../cdash/common.php');
    include_once('../models/project.php');

    global $CDASH_DOWNLOAD_RELATIVE_URL;

    if(!isset($this->Parameters['project']))
      {
      return array('status'=>false, 'message'=>'You must specify a project parameter.');
      }
    $projectid = get_project_id($this->Parameters['project']);
    if(!is_numeric($projectid) || $projectid <= 0)
      {
      return array('status'=>false, 'message'=>'Project not found.');
      }

    $Project = new Project();
    $Project->Id = $projectid;
    $files = $Project->GetUploadedFilesOrUrls();

    if(!$files)
      {
      return array('status'=>false, 'message'=>'Error in Project::GetUploadedFilesOrUrls');
      }
    $filteredList = array();
    foreach($files as $file)
      {
      if($file['isurl'])
        {
        continue; // skip if filename is a URL
        }
      if(isset($this->Parameters['match']) && !preg_match('/'.$this->Parameters['match'].'/', $file['filename']))
        {
        continue; //skip if it doesn't match regex
        }
      $filteredList[] = array_merge($file, array('url'=>$CDASH_DOWNLOAD_RELATIVE_URL.'/'.$file['sha1sum'].'/'.$file['filename']));

      if(isset($this->Parameters['mostrecent']))
        {
        break; //user requested only the most recent file
        }
      }

    return array('status'=>true, 'files'=>$filteredList);
    }

  /** Run function */
  function Run()
    {
    switch($this->Parameters['task'])
      {
      case 'list': return $this->ListProjects();
      case 'login': return $this->Authenticate();
      case 'files': return $this->ListFiles();
      }
    }
}

?>
