<?php
// To be able to access files in this CDash installation regardless
// of getcwd() value:
//
$cdashpath = dirname(dirname(__FILE__));
set_include_path($cdashpath . PATH_SEPARATOR . get_include_path());

require_once("cdash/dailyupdates.php");

$projectid = $_GET['projectid'];
addDailyChanges($projectid);
?>
