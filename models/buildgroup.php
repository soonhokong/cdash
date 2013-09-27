<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: buildgroup.php 3113 2012-01-25 16:23:31Z jjomier $
  Language:  PHP
  Date:      $Date: 2012-01-25 17:23:31 +0100 (mer., 25 janv. 2012) $
  Version:   $Revision: 3113 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
include_once('build.php');

class BuildGroup
{
  var $Id;
  var $Name;
  var $StartTime;
  var $EndTime;
  var $Description;
  var $SummaryEmail;
  var $ProjectId;

  function __construct()
    {
    $this->StartTime = '1980-01-01 00:00:00';
    $this->EndTime = '1980-01-01 00:00:00';
    $this->SummaryEmail = 0;
    }

  function SetPosition($position)
    {
    $position->GroupId = $this->Id;
    $position->Add();
    }

  function AddRule($rule)
    {
    $rule->GroupId = $this->Id;
    $rule->Add();
    }

  /** Get the next position available for that group */
  function GetNextPosition()
    {
    $query = pdo_query("SELECT bg.position FROM buildgroupposition as bg,buildgroup as g
                        WHERE bg.buildgroupid=g.id AND g.projectid='".$this->ProjectId."'
                        AND bg.endtime='1980-01-01 00:00:00'
                        ORDER BY bg.position DESC LIMIT 1");
    if(pdo_num_rows($query)>0)
      {
      $query_array = pdo_fetch_array($query);
      return $query_array['position']+1;
      }
    return 1;
    }

  /** Check if the group already exists */
  function Exists()
    {
    // If no id specify return false
    if(!$this->Id || !$this->ProjectId)
      {
      return false;
      }

    $query = pdo_query("SELECT count(*) AS c FROM buildgroup WHERE id='".$this->Id."' AND projectid='".$this->ProjectId."'");
    add_last_sql_error("BuildGroup:Exists",$this->ProjectId);
    $query_array = pdo_fetch_array($query);
    if($query_array['c']==0)
      {
      return false;
      }

    return true;
    }

  /** Save the group */
  function Save()
    {
    if($this->Exists())
      {
      // Update the project
      $query = "UPDATE buildgroup SET";
      $query .= " name='".$this->Name."'";
      $query .= ",projectid='".$this->ProjectId."'";
      $query .= ",starttime='".$this->StartTime."'";
      $query .= ",endtime='".$this->EndTime."'";
      $query .= ",description='".$this->Description."'";
      $query .= ",summaryemail='".$this->SummaryEmail."'";
      $query .= " WHERE id='".$this->Id."'";

      if(!pdo_query($query))
        {
        add_last_sql_error("BuildGroup:Update",$this->ProjectId);
        return false;
        }
      }
    else
      {
      $id = "";
      $idvalue = "";
      if($this->Id)
        {
        $id = "id,";
        $idvalue = "'".$this->Id."',";
        }

      if(!pdo_query("INSERT INTO buildgroup (".$id."name,projectid,starttime,endtime,description)
                     VALUES (".$idvalue."'$this->Name','$this->ProjectId','$this->StartTime','$this->EndTime','$this->Description')"))
        {
        add_last_sql_error("Buildgroup Insert",$this->ProjectId);
        return false;
        }
      
      if(!$this->Id)
        {
        $this->Id = pdo_insert_id("buildgroup");
        }

      // Insert the default position for this group
      // Find the position for this group
      $position = $this->GetNextPosition();
      pdo_query("INSERT INTO buildgroupposition(buildgroupid,position,starttime,endtime)
                 VALUES ('".$this->Id."','".$position."','".$this->StartTime."','".$this->EndTime."')");

      }
    } // end function save

  function GetGroupIdFromRule($build)
    {
    $name = $build->Name;
    $type = $build->Type;
    $siteid = $build->SiteId;
    $starttime = $build->StartTime;
    $projectid = $build->ProjectId;

    // Insert the build into the proper group
    // 1) Check if we have any build2grouprules for this build
    $build2grouprule = pdo_query("SELECT b2g.groupid FROM build2grouprule AS b2g, buildgroup as bg
                                  WHERE b2g.buildtype='$type' AND b2g.siteid='$siteid' AND b2g.buildname='$name'
                                  AND (b2g.groupid=bg.id AND bg.projectid='$projectid')
                                  AND '$starttime'>b2g.starttime
                                  AND ('$starttime'<b2g.endtime OR b2g.endtime='1980-01-01 00:00:00')");

    if(pdo_num_rows($build2grouprule)>0)
      {
      $build2grouprule_array = pdo_fetch_array($build2grouprule);
      return $build2grouprule_array["groupid"];
      }
    else // we don't have any rules we use the type
      {
      $buildgroup = pdo_query("SELECT id FROM buildgroup WHERE name='$type' AND projectid='$projectid'");
      $buildgroup_array = pdo_fetch_array($buildgroup);
      return $buildgroup_array["id"];
      }
    }

  // Return the value of summaryemail
  function GetSummaryEmail()
    {
    if(!$this->Id)
      {
      echo "BuildGroup GetSummaryEmail(): Id not set";
      return false;
      }
    $summaryemail = pdo_query("SELECT summaryemail FROM buildgroup WHERE id=".qnum($this->Id));
    if(!$summaryemail)
      {
      add_last_sql_error("BuildGroup GetSummaryEmail",$this->ProjectId);
      return false;
      }

    $summaryemail_array = pdo_fetch_array($summaryemail);
    return $summaryemail_array["summaryemail"];
    }

  // Return the value of emailcommitters, 0 or 1
  function GetEmailCommitters()
    {
    if(!$this->Id)
      {
      add_log('no BuildGroup Id, cannot query database, returning default value of 0',
        'BuildGroup::GetEmailCommitters', LOG_ERR);
      return 0;
      }

    $emailCommitters = pdo_get_field_value(
      "SELECT emailcommitters FROM buildgroup WHERE id=".qnum($this->Id),
      'emailcommitters', 0);

    return $emailCommitters;
    }
}

?>
