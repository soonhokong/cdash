<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: errorlog.php 3075 2011-12-25 23:20:58Z jjomier $
  Language:  PHP
  Date:      $Date: 2011-12-25 18:20:58 -0500 (Sun, 25 Dec 2011) $
  Version:   $Revision: 3075 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

/** ErrorLog */
class ErrorLog
{
  var $ProjectId;
  var $BuildId;
  var $Type;
  var $Description;
  var $ResourceType;
  var $ResourceId;

  function __construct()
    {
    $this->ProjectId = 0;
    $this->BuildId = 0;
    $this->ResourceId = 0;
    $this->ResourceType = 0;
    $this->Type = 0;
    }

  // Clean the logs more than one week
  function Clean($days,$projectid=0)
    {
    $time = time()-($days*3600*24);
    $date = date("Y-m-d H:i:s",$time);

    $sql = '';
    if(is_numeric($projectid) && $projectid > 0)
      {
      $sql = " AND projectid=".$projectid;
      }

    pdo_query("DELETE FROM errorlog WHERE date<'".$date."'".$sql);
    }

  // Save in the database
  function Insert()
    {
    if(!is_numeric($this->ProjectId) ||
       !is_numeric($this->BuildId) ||
       !is_numeric($this->ResourceId) ||
       !is_numeric($this->ResourceType) ||
       !is_numeric($this->Type))
      {
      return false;
      }

    $description = pdo_real_escape_string($this->Description);

    // If the projectid is not set but the buildid is we are trying to find
    // the projectid
    if($this->ProjectId == 0 && $this->BuildId>0)
      {
      $query = pdo_query("SELECT projectid FROM build WHERE id='".$this->BuildId."'");
      if(pdo_num_rows($query)>0)
        {
        $query_array = pdo_fetch_array($query);
        $this->ProjectId = $query_array['projectid'];
        }
      }

    // Insert a new row every time an error exists
    $now = date("Y-m-d H:i:s");

    $sql = "INSERT INTO errorlog (projectid,buildid,type,date,resourcetype,resourceid,description)
               VALUES ('".$this->ProjectId."','".$this->BuildId."','".$this->Type."','".
                         $now."','".$this->ResourceType."','".$this->ResourceId."','".$description."')";

    pdo_query($sql);
    echo pdo_error();

    // We don't log on purpose (loop loop ;)
    return true;
    }  // end insert

}

?>
