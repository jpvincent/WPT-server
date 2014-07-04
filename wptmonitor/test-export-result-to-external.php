<?php
include 'monitor.inc';
include 'alert_functions.inc';
include 'wpt_functions.inc';
include_once 'utils.inc';
require_once('bootstrap.php');

ini_set('display_errors', 'on');

$wptJobId = '27';


$label = makeLabelFromJobID($wptJobId);
/*
exportResultToExternal(
  array(
  	'results.'.$label.'.timeToTitle' =>   "250",
	  'results.'.$label.'.timeToFirstByte' =>   "10000",
	  'results.'.$label.'.nbBytesIn' => "90",
	  'results.'.$label.'.scoreGzip' =>  "90",
	  'results.'.$label.'.scoreCache' =>  "1500",
	  'results.'.$label.'.timeToRender' =>  "200",
	  'results.'.$label.'.nbHttpQueries' =>  "100",
	  'results.'.$label.'.nb404' => '0'
	)
);
*/

$resultId = '140704_VD_7P';
processResultsForAll($resultId);