<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: buildusernote.php 2600 2010-08-02 22:45:36Z jjomier $
  Language:  PHP
  Date:      $Date: 2010-08-03 00:45:36 +0200 (mar., 03 août 2010) $
  Version:   $Revision: 2600 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
// It is assumed that appropriate headers should be included before including this file
class BuildUserNote
{
  var $UserId;
  var $Note;
  var $TimeStamp;
  var $Status;
  var $BuildId;

  // Insert in the database
  function Insert()
    {
    if(!$this->BuildId)
      {
      echo "BuildUserNote::Insert(): BuildId is not set<br>";
      return false;
      }
      
    if(!$this->UserId)
      {
      echo "BuildUserNote::Insert(): UserId is not set<br>";
      return false;
      }

    if(!$this->Note)
      {
      echo "BuildUserNote::Insert(): Note is not set<br>";
      return false;
      }
      
    if(!$this->TimeStamp)
      {
      echo "BuildUserNote::Insert(): TimeStamp is not set<br>";
      return false;
      }
      
    if(!$this->Status)
      {
      echo "BuildUserNote::Insert(): Status is not set<br>";
      return false;
      }  
       
    $query = "INSERT INTO buildnote (buildid,userid,note,timestamp,status)
              VALUES ('$this->BuildId','$this->UserId','$this->Note','$this->TimeStamp','$this->Status')";                     
    if(!pdo_query($query))
      {
      add_last_sql_error("BuildUserNote Insert",0,$this->BuildId);
      return false;
      }  
    return true;
    }        
}
?>
