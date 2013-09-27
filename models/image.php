<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: image.php 3113 2012-01-25 16:23:31Z jjomier $
  Language:  PHP
  Date:      $Date: 2012-01-25 17:23:31 +0100 (mer., 25 janv. 2012) $
  Version:   $Revision: 3113 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
// It is assumed that appropriate headers should be included before including this file
class Image
{
  var $Id;
  var $Filename;
  var $Extension;
  var $Checksum;

  var $Data; // In the file refered by Filename
  var $Name; // Use to track the role for test


  function __construct()
    {
    $this->Filename = '';
    $this->Name = '';
    }

  private function GetData()
    {
    if(strlen($this->Filename)>0)
      {
      $h = fopen($this->Filename,"rb");
      $this->Data = addslashes(fread($h,filesize($this->Filename)));
      fclose($h);
      }
    }

  /** Check if exists */
  function Exists()
    {
    // If no id specify return false
    if($this->Id)
      {
      $query = pdo_query("SELECT count(*) AS c FROM image WHERE id='".$this->Id."'");
      $query_array = pdo_fetch_array($query);
      if($query_array['c']==0)
        {
        return false;
        }
      return true;
      }
    else
      {
      // Check if the checksum exists
      $query = pdo_query("SELECT id FROM image WHERE checksum='".$this->Checksum."'");
      if(pdo_num_rows($query)>0)
        {
        $query_array = pdo_fetch_array($query);
        $this->Id = $query_array['id'];
        return true;
        }
      return false;
      }
    return true;
    }

  /** Save the image */
  function Save()
    {
    include("cdash/config.php");
    // Get the data from the file if necessary
    $this->GetData();

    if(!$this->Exists())
      {
      $id = "";
      $idvalue = "";
      if($this->Id)
        {
        $id = "id,";
        $idvalue = "'".$this->Id."',";
        }

      $contents = $this->Data;
      if($CDASH_DB_TYPE == "pgsql")
        {
        $contents = pg_escape_bytea($this->Data);
        }

      if(!pdo_query("INSERT INTO image (".$id."img,extension,checksum)
                     VALUES (".$idvalue."'".$contents."','".$this->Extension."','".$this->Checksum."')"))
         {
         add_last_sql_error("Image::Save");
         return false;
         }

      if(!$this->Id)
        {
        $this->Id = pdo_insert_id("image");
        }

      }
    return true;
  } // end function save

}

?>
