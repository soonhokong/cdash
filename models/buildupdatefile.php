<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: buildupdatefile.php 3179 2012-02-13 08:59:24Z jjomier $
  Language:  PHP
  Date:      $Date: 2012-02-13 03:59:24 -0500 (Mon, 13 Feb 2012) $
  Version:   $Revision: 3179 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
// It is assumed that appropriate headers should be included before including this file
class BuildUpdateFile
{
  var $Filename;
  var $CheckinDate;
  var $Author;
  var $Email;
  var $Committer;
  var $CommitterEmail;
  var $Log;
  var $Revision;
  var $PriorRevision;
  var $Status; //MODIFIED | CONFLICTING | UPDATED
  var $UpdateId;

  // Insert the update
  function Insert()
    {
    if(strlen($this->UpdateId)==0)
      {
      echo "BuildUpdateFile:Insert UpdateId not set";
      return false;
      }

    $this->Filename = pdo_real_escape_string($this->Filename);

    // Sometimes the checkin date is not found in that case we put the usual date
    if($this->CheckinDate == "Unknown")
      {
      $this->CheckinDate = "1980-01-01";
      }

    if(strtotime($this->CheckinDate) === false && is_numeric($this->CheckinDate))
      {
      $this->CheckinDate = date(FMT_DATETIME,$this->CheckinDate);
      }
    else if(strtotime($this->CheckinDate) !== false)
      {
      $this->CheckinDate = date(FMT_DATETIME,strtotime($this->CheckinDate));
      }
    else
      {
      $this->CheckinDate = "1980-01-01";
      }
    $this->Author = pdo_real_escape_string($this->Author);
    $this->UpdateId = pdo_real_escape_string($this->UpdateId);

    // Check if we have a robot file for this build
    $robot = pdo_query("SELECT authorregex FROM projectrobot,build,build2update
                WHERE projectrobot.projectid=build.projectid
                AND build2update.buildid=build.id
                AND build2update.updateid=".qnum($this->UpdateId)." AND robotname='".$this->Author."'");

    if(pdo_num_rows($robot)>0)
      {
      $robot_array = pdo_fetch_array($robot);
      $regex = $robot_array['authorregex'];
      preg_match($regex,$this->Log,$matches);
      if(isset($matches[1]))
        {
        $this->Author = $matches[1];
        }
      }

    $this->Email = pdo_real_escape_string($this->Email);
    $this->Committer = pdo_real_escape_string($this->Committer);
    $this->CommitterEmail = pdo_real_escape_string($this->CommitterEmail);
    $this->Log = pdo_real_escape_string($this->Log);
    $this->Revision = pdo_real_escape_string($this->Revision);
    $this->PriorRevision = pdo_real_escape_string($this->PriorRevision);

    $query = "INSERT INTO updatefile (updateid,filename,checkindate,author,email,log,revision,priorrevision,status,committer,committeremail)
              VALUES (".qnum($this->UpdateId).",'$this->Filename','$this->CheckinDate','$this->Author','$this->Email',
                      '$this->Log','$this->Revision','$this->PriorRevision','$this->Status','$this->Committer','$this->CommitterEmail')";


    if(!pdo_query($query))
      {
      add_last_sql_error("BuildUpdateFile Insert",0,$this->UpdateId);
      return false;
      }
    } // end function insert
}

?>
