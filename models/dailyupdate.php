<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: dailyupdate.php 2724 2010-10-22 15:37:42Z jjomier $
  Language:  PHP
  Date:      $Date: 2010-10-22 17:37:42 +0200 (ven., 22 oct. 2010) $
  Version:   $Revision: 2724 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
class DailyUpdate
{
  var $Id;
  var $Date;
  var $Command;
  var $Type;
  var $Status;
  var $ProjectId;
  
  /** Get all the authors of a file */
  function GetAuthors($filename,$onlylast=false)
    {
    if(!$this->ProjectId)
      {
      echo "DailyUpdate::GetAuthors(): ProjectId is not set<br>";
      return false;
      }
      
    // Check if the note already exists   
    $filename = pdo_real_escape_string($filename);
    
    // Remove
    if(substr($filename,0,2) == './')
      {
      $filename = substr($filename,2);
      }
    
    $sql = "";
    if($onlylast)
      {
      $sql = " ORDER BY dailyupdate.id DESC LIMIT 1";
      }
    
    $query = pdo_query("SELECT DISTINCT up.userid FROM user2project AS up,user2repository AS ur,dailyupdatefile,dailyupdate 
                        WHERE dailyupdatefile.dailyupdateid=dailyupdate.id 
                        AND dailyupdate.projectid=up.projectid
                        AND ur.credential=dailyupdatefile.author
                        AND up.projectid=".qnum($this->ProjectId)." 
                        AND up.userid=ur.userid
                        AND (ur.projectid=0 OR ur.projectid=".qnum($this->ProjectId).")
                        AND dailyupdatefile.filename LIKE '%".$filename."'".$sql);                    
        
    if(!$query)
      {
      add_last_sql_error("DailyUpdate GetAuthors",$this->ProjectId);
      return false;
      }
    
    $authorids = array();
    while($query_array = pdo_fetch_array($query))
      {
      $authorids[] = $query_array['userid'];
      }   
    return $authorids;
    }
}
?>
