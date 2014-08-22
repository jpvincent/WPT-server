<?php
include 'monitor.inc';
include 'alert_functions.inc';
include 'wpt_functions.inc';
include_once 'utils.inc';
require_once('bootstrap.php');

ini_set('display_errors', 'on');

$wptJobId = '27';


/*
$label = makeLabelFromJobID($wptJobId);

exportResultToExternal(
	array(
		'scenario'	=> $label,
		'browserName' => 'Internet Explorer',
		'browserVersion' => '8.0.6001.18702',
		'timeToTitle' =>   "250",
		'timeToFirstByte' =>   "10000",
		'nbBytesIn' => "90",
		'scoreGzip' =>  "90",
		'scoreCache' =>  "1500",
		'timeToRender' =>  "200",
		'nbHttpQueries' =>  "100",
		'nb404' => '0'
	)
);
*/

$resultId = '140822_C8_A9';
processResultsForAll($resultId);