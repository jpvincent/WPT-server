<?php
  require("login/login.php");
  include 'monitor.inc';
  $jobIds = $_REQUEST['job_id'];
  $tests = "";
	foreach($jobIds as $job_id){
		if (!empty($tests)){
			$tests .=",";
		}
		$q = Doctrine_Query::create()->select('r.WPTResultId, r.WPTHost')
					->from('WPTResult r')
					->where('r.WPTJobId = ?', $job_id)
					->whereIn('r.Status', array('200','99999'))
					->orderBy('r.Date DESC')
					->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
					->limit(1);
		$job = $q->fetchOne();
		$tests .= $job['WPTResultId']."-r:1-c:0";

		// we suppose that we compare jobs on from the same WPT host
		$WPTHost = $job['WPTHost'];
	}

	// JPV: correct the path for screenshot comparison : comparison is made on the WPT host, not on wpt monitor ho
  //$location = getBaseURL()."/video/compare.php?tests=".$tests;
	$location = $WPTHost."/video/compare.php?tests=".$tests;
	header("Location: ".$location);
