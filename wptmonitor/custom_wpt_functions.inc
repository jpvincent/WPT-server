<?php
function makeLabelFromJobID($jobId)
{
	$folderId = getFolderIdFor($jobId, 'WPTJob');

	$job = Doctrine_Core::getTable('WPTJob')->find($jobId);

	$folderTable   = Doctrine_Core::getTable('WPTJobFolder');
	$folder        = $folderTable->find($folderId);
	$folder->Label = preg_replace('/éè/', 'e', $folder->Label);
	$alertLabel    = preg_replace('/[^a-zA-Z1-9]/', '_', $folder->Label);

	$job['Label'] = preg_replace('/[éè]/', 'e', $job['Label']);
	$alertLabel .= '.' . preg_replace('/[^a-zA-Z1-9éè]/', '_', $job['Label']);
	$alertLabel = preg_replace('/\.+/', '.', $alertLabel);
	$alertLabel = preg_replace('/_+/', '_', $alertLabel);

	logOutput('[LOG] [makeLabelFromJobID] Label:"' . $alertLabel . '" , Job:"' . $job['Label'] . '" , Folder:"' . $folder->Label . '"');
	return $alertLabel;
}


//
function exportResultToExternal($metric_name, $metric_value = null, $time = null)
{

	if ($time === null)
		$time = time();

	$body = ''; //date('r', $time);

	if (!is_array($metric_name)) {
		$metric_name = array(
			$metric_name => $metric_value
		);
	}

	// flatten if given a bunch of metrics
	foreach ($metric_name as $key => $value) {
		$body .= ' ' . $key . '=' . $value;
	}

	$metric_name = str_replace(' ', '.', $metric_name);

	$ch = curl_init('your-server');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	// to pass badly configured https sites
	curl_setopt($ch, CURLOPT_CERTINFO, true);
	// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_exec($ch);

	$info = curl_getinfo($ch);
	curl_close($ch);

}
