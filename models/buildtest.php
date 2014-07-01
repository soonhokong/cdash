<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: buildtest.php 2567 2010-07-27 15:07:22Z zach.mullen $
  Language:  PHP
  Date:      $Date: 2010-07-27 11:07:22 -0400 (Tue, 27 Jul 2010) $
  Version:   $Revision: 2567 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

/** Build Test class */          
class BuildTest
{
  var $TestId;
  var $Status;
  var $Time;
  var $TimeMean;
  var $TimeStd;
  var $TimeStatus;
  var $BuildId;  

  // Insert in the database
  function Insert()
    {
    if(!$this->BuildId)
      {
      add_log('BuildId is not set','BuildTest::Insert()',LOG_ERR,0,$buildid);
      return false;
      }

    if(!$this->TestId)
      {
      add_log('TestId is not set','BuildTest::Insert()',LOG_ERR,0,$buildid);
      return false;
      }
    
    if(empty($this->Time))
      {
      $this->Time = 0;
      } 
    if(empty($this->TimeMean))
      {
      $this->TimeMean = 0;
      } 
    if(empty($this->TimeStd))
      {
      $this->TimeStd = 0;
      } 
    if(empty($this->TimeStatus))
      {
      $this->TimeStatus = 0;
      } 
        
    $query = "INSERT INTO build2test (buildid,testid,status,time,timemean,timestd,timestatus)
                 VALUES (".qnum($this->BuildId).",".qnum($this->TestId).",'$this->Status',".qnum($this->Time).","
                          .qnum($this->TimeMean).",".qnum($this->TimeStd).",".qnum($this->TimeStatus).")";                     
    if(!pdo_query($query))
      {
      add_last_sql_error("BuildTest:Insert",0,$this->BuildId);
      return false;
      }  
    return true;
    } 
    
  /** Get the number of tests that are failing */
  function GetNumberOfFailures($checktesttiming,$testtimemaxstatus)
    {
    if(!$this->BuildId)
      {
      echo "BuildTest::GetNumberOfFailures(): BuildId not set";
      return false;    
      }
      
    $sql = "SELECT testfailed,testnotrun,testtimestatusfailed FROM build WHERE id=".qnum($this->BuildId);
    $query = pdo_query($sql);
    if(!$query)
      {
      add_last_sql_error("BuildTest:GetNumberOfFailures",0,$this->BuildId);
      return false;
      }  

    $nfail_array = pdo_fetch_array($query);
    
    $sumerrors = 0;
    if($nfail_array['testfailed']>0)
      {
      $sumerrors += $nfail_array['testfailed'];
      }
    if($nfail_array['testnotrun']>0)
      {
      $sumerrors += $nfail_array['testnotrun'];
      }
        
    // Find if the build has any test failings
    if($checktesttiming)
      {
      if($nfail_array['testtimestatusfailed']>0)
        {
        $sumerrors +=  $nfail_array['testtimestatusfailed'];
        }
      }
    return $sumerrors;
    } // end GetNumberOfFailures()   
}
?>
