<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: index.php 3052 2011-12-22 15:20:55Z jjomier $
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

require_once("cdash/pdo.php");

// Add other api includes here
require("api_coverage.php");
require("api_project.php");
require("api_build.php");
require("api_user.php");
require("api_repository.php");

if(!isset($_GET['method']))
  {
  echo "Method should be set: method=...";
  return;
  }
$method = $_GET['method'];

$classname = ucfirst($method).'API';
$class = new $classname;
$class->Parameters = array_merge($_GET, $_POST);
$results = $class->Run();

// Return json by default
echo json_encode($results);
?>
