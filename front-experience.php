<?php
include 'common.inc';
include 'object_detail.inc'; 
require_once('page_data.inc');
require_once('waterfall.inc');
include 'cpuUsage.inc';


$page_keywords = array('Domains','Webpagetest','Website Speed Test');
$page_description = "Website domain breakdown$testLabel";

$runData = loadPageRunData($testPath, $run, $cached);


$cpuMetrics = GetCPUMetrics($testPath, $run,  $id);

?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>WebPagetest Frontend user experience<?php echo $testLabel; ?></title>
		<?php include ('head.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Test Result';
            $subtab = 'User Experience';
            include 'header.inc';
            ?>
			<h2>Frontend User Experience</h2>
			<h3>CPU usage</h3>
			<table align="center">
				<tr>
					<th colspan="2">Browser Freezes*</th>
					<td><?= $cpuMetrics['numberOfFreeze']; ?></td>
				</tr>
				<tr>
					<tr>
						<th colspan="2">CPU cost*</th>
						<td><?= $cpuMetrics['CPUCost']; ?></td>
					</tr>
				</tr>
				<tr>
					<tr>
						<th colspan="2">Average load</th>
						<td><?= $cpuMetrics['averageLoad']; ?>%</td>
					</tr>
				</tr>
				<tr>
					<tr>
						<th colspan="2">Median load*</th>
						<td><?= $cpuMetrics['medianLoad']; ?>%</td>
					</tr>
				</tr>
			</table>
			
			<h3>Code quality</h3>
			<table align="center">
				<?php if( $runData['domElements'] > 0 ) { ?>
				<tr>
					<th colspan="2">DOM elements</th>
					<td><?= $runData['domElements']; ?></td>
				</tr>
				<?php } ?>
			</table>
			<ul>
				<li>* Browser Freezes = the number of time the CPU was 100% during more than half a second. That generally means that the UI is frozen during noticable periods. Eg: a number of 4 could represent a full 2s freeze or 4 half a second freezes. 0 would mean the interface never really blocks.
				</li>
				<li>*CPU cost = this number is simply the sum of the CPU usage taken every 100ms. A low number could mean that you page loaded very fast or with very few CPU peaks. Solely, it's not very meaningful, but it's important to monitor this figure to detect heavy ads introduction or a bad usage of JavaScript.</li>
				<li>*Median and average Load : If the median is far from the average, that means that your CPU load has huge peaks followed by quiet periods. Eg: 40 avg and 40 median : your page consumes 40% of the CPU all the time. 40 average and 20 median : there is peaks, see CPU hogs </li>
				
			</ul>
		</div>
    </body>
</html>

<?php

