<?php
require_once('draw.inc');

function GetCPUMetrics($testPath, $run) {
	
	$filename = $testPath.'/'.$run.'_progress.csv';
	$perfs = LoadPerfData($filename, true, false);

	$CPUCost = 0;
	$numberOfHogs = 0;
	$startHogTimestamp = 0;

	foreach($perfs['cpu']['data'] as $timestamp => $usage ) {
		$CPUCost += $usage;

		if($usage == 100) {
			if($startHogTimestamp === 0) { // first time, mark the time
				$startHogTimestamp = $timestamp;
			} else { // second time or more ? see if it's lasting since more than 500ms
				if( ($timestamp - $startHogTimestamp) > 500) {
					$numberOfHogs++; // user is frozen since more that half a second, we count that as a hog
					$startHogTimestamp = 0; // reset
				}
			}
		} else { // CPU cooled down, reset the Hog marker
			$startHogTimestamp = 0;
		}
	}
	
    return array(
    	'CPUCost'	=> $CPUCost,
		'numberOfFreeze'	=> $numberOfHogs,
		'averageLoad'	=> round($CPUCost / count($perfs['cpu']['data'])),
		'medianLoad'	=> calculate_median($perfs['cpu']['data']),
		'rawData'	=> $perfs['cpu']['data']
    );
}

function calculate_median($arr) {
    sort($arr);
    $count = count($arr); //total numbers in array
    $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
    if($count % 2) { // odd number, middle is the median
        $median = $arr[$middleval];
    } else { // even number, calculate avg of 2 medians
        $low = $arr[$middleval];
        $high = $arr[$middleval+1];
        $median = (($low+$high)/2);
    }
    return $median;
}