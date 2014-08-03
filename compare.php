<?php
include 'common.inc';
include 'object_detail.inc'; 
require_once('page_data.inc');
require_once('waterfall.inc');

$page_keywords = array('Performance Test','Details','Webpagetest','Website Speed Test','Page Speed');
$page_description = "Website performance test comparison";
$fvonly = false;

$metrics = array(   'docTime' => 'Load Time (onload)',
                    'fullyLoaded' => 'Load Time (Fully Loaded)',
                    'TTFB' => 'First Byte Time',
                    'render' => 'Time to First Paint',
                    'requestsDoc' => 'Requests',
                    'bytesInDoc' => 'Bytes In');

// load the data for each of the tests
$tests = array();
$fv_max_time = 0;
if (array_key_exists('tests', $_REQUEST)) {
    $parts = explode(',', $_REQUEST['tests']);
    foreach($parts as $fragment) {
        $test = array();
        $components = explode('-', $fragment);
        $test['id'] = trim($components[0]);
        $test['path'] = './' . GetTestPath($test['id']);
        $test['info'] = json_decode(gz_file_get_contents("{$test['path']}/testinfo.json"), true);
        $test['page_data'] = loadAllPageData($test['path']);
        $test['fv_run'] = GetMedianRun($test['page_data'], 0, $median_metric);
        if ($test['fv_run']) {
            $test['fv'] = $test['page_data'][$test['fv_run']][0];
            $docTime = $test['fv']['docTime'] / 1000;
            if ($docTime > $fv_max_time)
                $fv_max_time = $docTime;
        }
        $test['rv_run'] = GetMedianRun($test['page_data'], 1, $median_metric);
        if ($test['rv_run']) {
            $test['rv'] = $test['page_data'][$test['rv_run']][0];
        } else {
            $fvonly = true;
        }
        $tests[] = $test;
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>WebPagetest Test Comparison</title>
        <?php $gaTemplate = 'Compare'; include ('head.inc'); ?>
        <style type="text/css">
            td {
                text-align:center; 
                vertical-align:middle; 
                padding:1em;
            }

            div.bar {
                height:12px; 
                margin-top:auto; 
                margin-bottom:auto;
            }

            td.legend {
                white-space:nowrap; 
                text-align:left; 
                vertical-align:top; 
                padding:0;
            }
            
            div.container {
                position: relative; left: 0; top: 0;
            }
            
            img.base {
                position: relative; left: 0; top: 0;
            }
            img.draggable {
                opacity:0.5;
                position: absolute; top: 0; left: 0;
            }
            #slider { margin: 10px; }
        </style>
    </head>
    <body>
        <div class="page">
            <?php
            include 'header.inc';
            ?>
            
            <div id="result">
                <div class="cleared"></div>
                <div style="text-align:center;">
                <?php
                // data tables
                echo '<h2>First View</h2>';
                echo "<table>\n<tr><th>Metric</th>";
                $index = 0;
                foreach ($tests as &$test) {
                    $index++;
                    echo "<th>Test $index</th>";
                }
                echo "</tr>\n";
                foreach ($metrics as $metric => $label) {
                    echo "<tr><td>$label</td>";
                    foreach ($tests as &$test) {
                        echo '<td>';
                        if ($test['fv_run']) {
                            $value = $test['fv'][$metric];
                            if (stripos($label, 'Time') !== false) {
                                $value = number_format($value / 1000.0, 3) . 's';
                            } elseif (stripos($label, 'Bytes') !== false) {
                                $value = number_format(round($value / 1024.0), 0) . ' KB';
                            }
                            echo $value;
                        }
                        echo '</td>';
                    }
                    echo "</tr>\n";
                }
                echo "</table>\n";
                
                // waterfalls
                if (count($tests) == 2) {
                    echo '<input id="slider" type="range"  min="0" max="100" value="50" />';
                    echo '<div class="container">';
                    echo "<img class=\"progress base\" alt=\"Page load waterfall diagram\" src=\"".$GLOBALS['basePath']."waterfall.php?test={$tests[0]['id']}&run={$tests[0]['fv_run']}&max=$fv_max_time&cached=0&cpu=0&bw=0\"><br>";
                    echo "<img class=\"progress draggable\" alt=\"Page load waterfall diagram\" src=\"".$GLOBALS['basePath']."waterfall.php?test={$tests[1]['id']}&run={$tests[1]['fv_run']}&max=$fv_max_time&cached=0&cpu=0&bw=0\"><br>";
                    echo "</div>";
                }
                ?>
                </div>
            </div>
            
            <?php include('footer.inc'); ?>
            <script type="text/javascript">
            $(function() {
                $("img.draggable").draggable();
                $("#slider").change( function() {
                    var value = $("#slider").val() / 100.0;
                    $("img.draggable").css('opacity', value);
                });
            });
            </script>
        </div>
    </body>
</html>
