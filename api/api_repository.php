<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: api_repository.php 3052 2011-12-22 15:20:55Z jjomier $
  Language:  PHP
  Date:      $Date: 2011-12-22 16:20:55 +0100 (jeu., 22 déc. 2011) $
  Version:   $Revision: 3052 $

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

class RepositoryAPI extends CDashAPI
{
  /** return the example URL  */
  private function ExampleURL()
    {
    include_once('../cdash/common.php');
    include_once('../cdash/repository.php');

    if(!isset($this->Parameters['url']))
      {
      echo "url parameter not set";
      return;
      }
    if(!isset($this->Parameters['type']))
      {
      echo "type parameter not set";
      return;
      }

    $url = $this->Parameters['url'];
    $functionname = 'get_'.strtolower($this->Parameters['type']).'_diff_url';
    return $functionname($url, 'DIRECTORYNAME', 'FILENAME', 'REVISION');
    }

  /** Run function */
  function Run()
    {
    switch($this->Parameters['task'])
      {
      case 'exampleurl': return $this->ExampleURL();
      }
    }
}

?>
