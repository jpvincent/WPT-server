<?php 
// Will clear the temporary folders of both WPT and WPTMonitor. Consequence is that the queue is cleared
function clearFolder($folder) {
	$dossier=opendir($folder);
	while ($File = readdir($dossier)) {
		if ($File != '.' && $File != '..') {
			$Vidage= $folder.$File;
			unlink($Vidage);
			rmdir($Vidage);
			echo '<p>'.$Vidage.' is now empty</p>';
		}
	}
	closedir($dossier);
}
// Known folders to clear
clearFolder('tmp/');
clearFolder('work/jobs/');
clearFolder('wptmonitor/template_c/');
clearFolder('wptmonitor/graph/cache/');
