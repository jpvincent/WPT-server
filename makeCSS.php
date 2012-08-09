<?php
include 'common.inc';

$files = array(
	'Details'	=> array(
		'pagestyle.css',
		'waterfall.css',
	),
	'Default'	=> array(
		'pagestyle.css',
	),
	
	'timeline'	=> array(
		'pagestyle.css',
		'timeline/auditsPanel.css',
		'timeline/popover.css',
		'timeline/dialog.css',
		'timeline/index.css',
		'timeline/inspector.css',
		'timeline/timelinePanel.css',
		'timeline/textViewer.css',
		'timeline/textPrompt.css',
		'timeline/tabbedPane.css',
		'timeline/scriptsPanel.css',
		'timeline/revisionHistory.css',
		'timeline/resourceView.css',
		'timeline/resourcesPanel.css',
		'timeline/profilesPanel.css',
		'timeline/networkPanel.css',
		'timeline/navigatorView.css',
		'timeline/inspectorCommon.css',
		'timeline/helpScreen.css',
		'timeline/heapProfiler.css',
		'timeline/elementsPanel.css',
		'timeline/devTools.css',
		'timeline/dataGrid.css',
		'timeline/auditsPanel.css',
		'timeline/panelEnablerView.css',
		'timeline/networkLogView.css',
		'timeline/nativeMemoryProfiler.css',
		'timeline/inspectorSyntaxHighlight.css',
		'timeline/indexedDBViews.css',
		'timeline/filteredItemSelectionDialog.css',
		'timeline/splitView.css',
	),
	'pagespeed'	=> array(
		'pagestyle.css',
		'widgets/pagespeed/tree.css',
	)
);

header('Content-Type: text/css;charset="UTF-8"');

/*if(!isset($_GET['page']))
	return header('HTTP/1.1 404 Not Found');*/
$page = $_GET['page'];
if(!isset($files[$page]))
	$page = 'Default';

// check for cache
$cacheFileName = $tempDir.'css.cache.'.$page.'.css';
if(file_exists($cacheFileName))
	exit( file_get_contents($cacheFileName));

$css = '';
// massive concatenation
foreach($files[$page] as $filename) {
	$css .= "\n".file_get_contents('./'.$filename);
}

// image path prefixing
$css = preg_replace('/url\(\w?(["\'])?/', 'url($1'.$GLOBALS['basePath'], $css );

print $css;

// record cache
file_put_contents($cacheFileName, $css);
