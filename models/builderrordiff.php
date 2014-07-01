<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: builderrordiff.php 2597 2010-08-02 22:30:42Z jjomier $
  Language:  PHP
  Date:      $Date: 2010-08-02 18:30:42 -0400 (Mon, 02 Aug 2010) $
  Version:   $Revision: 2597 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

/** BuildErrorDiff */
class BuildErrorDiff
{
  var $BuildId;
  var $Type;
  var $DifferencePositive;
  var $DifferenceNegative;

  /** Return if exists */
  function Exists()
    {
    if(!$this->BuildId || !is_numeric($this->BuildId))
      {
      echo "BuildErrorDiff::Save(): BuildId not set<br>";
      return false;    
      }

    if(!$this->Type || !is_numeric($this->Type))
      {
      echo "BuildErrorDiff::Save(): Type not set<br>";
      return false;    
      }
        
    $query = pdo_query("SELECT count(*) AS c FROM builderrordiff WHERE buildid='".$this->BuildId."' AND type='".$this->Type."'");  
    $query_array = pdo_fetch_array($query);
    if($query_array['c']>0)
      {
      return true;
      }
    return false;
    }      
      
  // Save in the database
  function Save()
    {
    if(!$this->BuildId || !is_numeric($this->BuildId))
      {
      echo "BuildErrorDiff::Save(): BuildId not set<br>";
      return false;    
      }

    if(!$this->Type || !is_numeric($this->Type))
      {
      echo "BuildErrorDiff::Save(): Type not set<br>";
      return false;    
      }

    if(!$this->DifferencePositive || !is_numeric($this->DifferencePositive))
      {
      echo "BuildErrorDiff::Save(): DifferencePositive not set<br>";
      return false;    
      }
      
    if(!$this->DifferenceNegative || !is_numeric($this->DifferenceNegative))
      {
      echo "BuildErrorDiff::Save(): DifferenceNegative not set<br>";
      return false;    
      }  
    if($this->Exists())
      {
      // Update
      $query = "UPDATE builderrordiff SET ";
      $query .= "difference_positive='".$this->DifferencePositive."'";
      $query .= ", difference_negative='".$this->DifferenceNegative."'";
      $query .= " WHERE buildid='".$this->BuildId."' AND type='".$this->Type."'";
      if(!pdo_query($query))
        {
        add_last_sql_error("BuildErrorDiff Update",0,$this->BuildId);
        return false;
        }
      }
    else // insert
      {    
      $query = "INSERT INTO builderrordiff (buildid,type,difference_positive,difference_negative)
                 VALUES ('".$this->BuildId."','".$this->Type."','".$this->DifferencePositive."','".
                           $this->DifferenceNegative."')";                     
      if(!pdo_query($query))
        {
        add_last_sql_error("BuildErrorDiff Create",0,$this->BuildId);
        return false;
        }  
      }
    return true;
    }      
}
?>
