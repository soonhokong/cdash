<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: buildgrouprule.php 2595 2010-08-02 22:16:13Z jjomier $
  Language:  PHP
  Date:      $Date: 2010-08-03 00:16:13 +0200 (mar., 03 août 2010) $
  Version:   $Revision: 2595 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
// It is assumed that appropriate headers should be included before including this file
class BuildGroupRule
{
  var $BuildType;
  var $BuildName;
  var $SiteId;
  var $Expected;
  var $StartTime;
  var $EndTime;  
  var $GroupId;  

  function __construct()
    {
    $this->StartTime = '1980-01-01 00:00:00';
    $this->EndTime = '1980-01-01 00:00:00';
    }
    
  /** Check if the rule already exists */  
  function Exists()
    {
    // If no id specify return false
   if(empty($this->GroupId) || empty($this->BuildType)
      || empty($this->BuildName) || empty($this->SiteId))
      {
      return false;    
      } 
    
    $query = pdo_query("SELECT count(*) AS c FROM build2grouprule WHERE 
                        groupid='".$this->GroupId."' AND buildtype='".$this->BuildType."'
                        AND buildname='".$this->BuildName."'
                        AND siteid='".$this->SiteId."'
                        AND starttime='".$this->StartTime."'
                        AND endtime='".$this->EndTime."'"
                        );
    $query_array = pdo_fetch_array($query);
    if($query_array['c']==0)
      {
      return false;
      }
    return true;  
    }  
    
  /** Save the goup position */
  function Add()
    {
    if(empty($this->GroupId) || empty($this->BuildType)
       || empty($this->BuildName) || empty($this->SiteId) || empty($this->Expected))
      {
      return false;    
      }  

    if(!$this->Exists())
      {
      if(!pdo_query("INSERT INTO build2grouprule (groupid,buildtype,buildname,siteid,expected,starttime,endtime)
                     VALUES ('$this->GroupId','$this->BuildType','$this->BuildName','$this->SiteId','$this->Expected','$this->StartTime','$this->EndTime')"))
         {
         add_last_sql_error("BuildGroupRule Insert()");
         return false;
         }
       return true;
      }  
    return false;
    } // end function add 
        
}
?>
