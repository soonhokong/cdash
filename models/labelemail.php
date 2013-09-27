<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: labelemail.php 2585 2010-08-02 16:12:10Z zach.mullen $
  Language:  PHP
  Date:      $Date: 2010-08-02 18:12:10 +0200 (lun., 02 août 2010) $
  Version:   $Revision: 2585 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
// It is assumed that appropriate headers should be included before including this file
class LabelEmail
{
  var $UserId;
  var $ProjectId;
  var $LabelId;
  
  function __construct()
    {
    $this->ProjectId = 0;
    $this->UserId = 0;
    $this->LabelId = 0;
    }
  
  /** Return if a project exists */
  function Exists()
    {
    // If no id specify return false
    if(!$this->ProjectId || !$this->UserId)
      {
      return false;    
      }
      
    $query = pdo_query("SELECT count(*) FROM labelemail WHERE userid=".qnum($this->UserId).
                        " AND projectid=".qnum($this->ProjectId).
                        " AND labelid=".qnum($this->LabelId));  
    $query_array = pdo_fetch_array($query);
    if($query_array[0]>0)
      {
      return true;
      }
    return false;
    }
    
  function Insert()
    {
    if(!$this->ProjectId)
      {
      echo "LabelEmail Insert(): ProjectId not set";
      return false;
      } 
      
    if(!$this->UserId)
      {
      echo "LabelEmail Insert(): UserId not set";
      return false;
      }
      
    if(!$this->LabelId)
      {
      echo "LabelEmail Insert(): LabelId not set";
      return false;
      } 
  
    if(!$this->Exists())
      {
      $query = pdo_query("INSERT INTO labelemail (userid,projectid,labelid) VALUES(".qnum($this->UserId).
                          ",".qnum($this->ProjectId).
                          ",".qnum($this->LabelId).")");  
      if(!$query)
        {
        return false;
        }
      }
      
    return true;
    } // end insert() function
  
  /** Update the labels given a projectid and userid */
  function UpdateLabels($labels)
    {
    if(!$this->ProjectId)
      {
      echo "LabelEmail UpdateLabels(): ProjectId not set";
      return false;
      } 
      
    if(!$this->UserId)
      {
      echo "LabelEmail UpdateLabels(): UserId not set";
      return false;
      } 
    
    if(!$labels)
      {
      $labels = array();
      }
      
    $existinglabels = $this->GetLabels();
    $toremove = array_diff($existinglabels,$labels);
    $toadd = array_diff($labels,$existinglabels);
    
    foreach($toremove as $id)
      {
      $this->LabelId = $id;
      $this->Remove();
      }
    
     foreach($toadd as $id)
      {
      $this->LabelId = $id;
      $this->Insert();
      }
    return true;  
    }
    
   
  /** Get the labels given a projectid and userid */
  function GetLabels()
    {
    if(empty($this->ProjectId))
      {
      echo "LabelEmail GetLabels(): ProjectId not set";
      return false;
      } 
      
    if(empty($this->UserId))
      {
      echo "LabelEmail GetLabels(): UserId not set";
      return false;
      } 
    
    $labels = pdo_query("SELECT labelid FROM labelemail WHERE projectid=".qnum($this->ProjectId)." AND userid=".qnum($this->UserId));
    if(!$labels)
      {
      add_last_sql_error("LabelEmail GetLabels");
      return false;
      }
    
    $labelids = array();
    while($labels_array = pdo_fetch_array($labels))
      {
      $labelids[] = $labels_array['labelid'];
      }
      
    return $labelids;  
    }

} // end class LabelEmail
?>
