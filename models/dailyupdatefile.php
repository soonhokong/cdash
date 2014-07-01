<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: dailyupdatefile.php 2602 2010-08-02 22:53:35Z jjomier $
  Language:  PHP
  Date:      $Date: 2010-08-02 18:53:35 -0400 (Mon, 02 Aug 2010) $
  Version:   $Revision: 2602 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
// It is assumed that appropriate headers should be included before including this file

class DailyUpdateFile
{ 
  var $Filename;
  var $CheckinDate;
  var $Author;
  var $Log;
  var $Revision;
  var $PriorRevision;
  var $DailyUpdateId;

  /** Check if exists */  
  function Exists()
    {
    // If no id specify return false
    if(!$this->DailyUpdateId || !$this->Filename)
      {
      return false;    
      }
    
    $query = pdo_query("SELECT count(*) AS c FROM dailyupdatefile WHERE dailyupdateid='".$this->DailyUpdateId."' AND filename='".$this->Filename."'");
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
    if(!$this->DailyUpdateId)
      {
      echo "DailyUpdateFile::Save(): DailyUpdateId not set!";
      return false;
      }
    
    if(!$this->Filename)
      {
      echo "DailyUpdateFile::Save(): Filename not set!";
      return false;
      }

    if(!$this->CheckinDate)
      {
      echo "DailyUpdateFile::Save(): CheckinDate not set!";
      return false;
      }
      
    if($this->Exists())
      {
      // Update the project
      $query = "UPDATE dailyupdatefile SET";
      $query .= " checkindate='".$this->CheckinDate."'";
      $query .= ",author='".$this->Author."'";
      $query .= ",log='".$this->Log."'";
      $query .= ",revision='".$this->Revision."'";
      $query .= ",priorrevision='".$this->PriorRevision."'";
      $query .= " WHERE dailyupdateid='".$this->DailyUpdateId."' AND filename='".$this->Filename."'";
      
      if(!pdo_query($query))
        {
        add_last_sql_error("DailyUpdateFile Update");
        return false;
        }
      }
    else
      {                                              
      if(!pdo_query("INSERT INTO dailyupdatefile (dailyupdateid,filename,checkindate,author,log,revision,priorrevision)
                     VALUES ('$this->DailyUpdateId','$this->Filename','$this->CheckinDate','$this->Author','$this->Log',
                     '$this->Revision','$this->PriorRevision')"))
         {
         add_last_sql_error("DailyUpdateFile Insert");
         return false;
         }
      }
    return true;
    } // end function save    
}


?>
