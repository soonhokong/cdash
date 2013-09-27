<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: buildinformation.php 2764 2010-11-04 15:05:48Z jjomier $
  Language:  PHP
  Date:      $Date: 2010-11-04 16:05:48 +0100 (jeu., 04 nov. 2010) $
  Version:   $Revision: 2764 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
class BuildInformation
{
  var $BuildId;
  var $OSName;
  var $OSPlatform;
  var $OSRelease;
  var $OSVersion;
  var $CompilerName = 'unknown';
  var $CompilerVersion = 'unknown';
  
  function SetValue($tag,$value)  
    {
    switch($tag)
      { 
      case "OSNAME": $this->OSName = $value;break;
      case "OSRELEASE": $this->OSRelease = $value;break;
      case "OSVERSION": $this->OSVersion = $value;break;
      case "OSPLATFORM": $this->OSPlatform = $value;break;
      case "COMPILERNAME": $this->CompilerName = $value;break;
      case "COMPILERVERSION": $this->CompilerVersion = $value;break;
      }
    }
 
    
  /** Save the site information */
  function Save()
    {  
    if($this->OSName!="" || $this->OSPlatform!="" || $this->OSRelease!="" || $this->OSVersion!="")
       {
       if(empty($this->BuildId))
         {
         return false;
         }
       
       // Check if we already have a buildinformation for that build. If yes we just skip it
       $query = pdo_query("SELECT buildid FROM buildinformation WHERE buildid=".qnum($this->BuildId));
       add_last_sql_error("BuildInformation Insert",0,$this->BuildId);
       if(pdo_num_rows($query)==0)
         {  
         pdo_query ("INSERT INTO buildinformation (buildid,osname,osrelease,osversion,osplatform,compilername,compilerversion) 
                    VALUES (".qnum($this->BuildId).",'$this->OSName','$this->OSRelease',
                            '$this->OSVersion','$this->OSPlatform','$this->CompilerName','$this->CompilerVersion')");
         add_last_sql_error("BuildInformation Insert",0,$this->BuildId);
         }
       return true;
       }
    } // end function save  
}
?>
