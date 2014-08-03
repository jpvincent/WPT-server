<?php
$files = array(
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
	'waterfall.css',
	'pagestyle.css',
	'widget/pagespeed/tree.css'
	'splitView.css',
);

$css = '';
// massive concatenation
foreach($files as $filename) {
	$css += "\n".file_get_contents('./'.$filename);
}

header('Content-Type: text/css;charset="UTF-8"');
return $css;

