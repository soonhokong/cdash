<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: svninfo.php 2890 2011-04-14 22:08:28Z david.cole $
  Language:  PHP
  Date:      $Date: 2011-04-15 00:08:28 +0200 (ven., 15 avr. 2011) $
  Version:   $Revision: 2890 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

require_once('login.php');


function echo_svn_output($cmd)
{
  // Assumes being able to run 'svn' on the web server in the CDash
  // directory...
  //
  $svn_output = `svn $cmd`;

  echo '<h3>svn ' . $cmd . '</h3>';
  echo '<pre>';
  echo htmlentities($svn_output);
  echo '</pre>';
  echo '<br/>';
}


function echo_file_contents($filename)
{
  // Emit the contents of the named file, but only if it exists.
  // If it doesn't exist, emit nothing.
  //
  if (file_exists($filename))
    {
    $contents = file_get_contents($filename);

    echo '<h3>contents of "'.$filename.'"</h3>';
    echo '<pre>';
    echo htmlentities($contents);
    echo '</pre>';
    echo '<br/>';
    }
}


if ($session_OK)
  {
  $userid = $_SESSION['cdash']['loginid'];

  $user_is_admin = pdo_get_field_value(
    "SELECT admin FROM " . qid("user") . " WHERE id='$userid'",
    'admin',
    0);

  if ($user_is_admin)
    {
    echo_svn_output('--version');
    echo_svn_output('info');
    echo_svn_output('status');
    echo_svn_output('diff');

    global $CDASH_ROOT_DIR;
    echo_file_contents($CDASH_ROOT_DIR.'/cdash/config.local.php');
    echo_file_contents($CDASH_ROOT_DIR.'/tests/config.test.local.php');

    echo '<h3>phpinfo</h3>';
    phpinfo();
    echo '<br/>';
    }
  else
    {
    echo 'Admin login required to display svn info.';
    }
  }

?>
