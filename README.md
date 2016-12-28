Why?
-

This project is a fork of both [WebPageTest Private Instance 2.19](https://github.com/WPO-Foundation/webpagetest/releases/tag/WebPageTest-2.19) and [WPT Monitor](http://www.wptmonitor.org/). The agents are from the 2.19 version. The original intention was to patch WPT monitor who looked a bit abandonned. Then, several customers of mine needed Private installs to monitor their front-end performances on a regular basis so I decided to provide a single package and give it access to anyone interested in Web Performance Monitoring.

Reliable ?
-

It's used currently in three major organizations running dozen of tests per minutes on dozen of pages.
Also, I tend to follow closely the WebPageTest upgrades.

Features
-

Additionally to the WPT and WPT monitor original features :
* loads of WPT Monitor bug fixes
* WPT results have a new metric : user experience, which tries to measure the number of time the page is freezing the browser.
* added a placeholder to send the WPT monitor results to another monitoring tool (a Graphite server as an example)
* changed some default values:
    * sharding is on (your tests go faster if you have several agents)
    * taking screenshots is on by default on WPT
    * 3 runs instead of 1
* Debugging WPT Monitor :
    * more debug logs
    * less useless error logs
    * manually clear the queue with unlock.php
    * about page now display the version number
* Scalability
    * limit size of jobProcessor_log.html to 100K
    * WPT monitor uses MySQL by default
    * Reports listing now works with dozen of scenarios without eating the whole memory
* WPT Monitor uses Curl instead of HttpRequest (better PHP compatibility)


Install
-
On a LAMP server :
* get the zip file.
* See the [https://sites.google.com/a/webpagetest.org/docs/private-instances](WPT private instance) installation documentation for requirements and first steps. The regular WPT must work, with agents running.
* have a mysql running, with a database named `wpt`
* edit `wptmonitor/settings/bootstrap.ini` for the mysql configuration
* execute `/wptmonitor/install.php` and follow [http://www.wptmonitor.org/home/installation](the old but still accurate documentation).
* setup cron jobs as per the above documentation.


Extensible
-

WPT monitor is super useful to schedule tests but not that good at displaying the collected data. You might also want to monitor additional data like the number of 404, the average size, the percentage of cached objects or the percentage of objects with gzip. Files to edit :
* `wptmonitor/custom_wpt_functions.inc` for the labelling and results exports to another monitoring tool if you need to.
* Search for the `exportResultToExternal()` calls, to enable or disable more logging
* for debug, uncomment the `echo` in the `logOutput` function, in the `wptmonitor/utils.inc`. Also simply monitor the apache error logs ("undefined index" notices are considered as normal-but-not-ideal for now)


Support and issues
-

The code works on the few installations I made, so I cant guarantee it works everywhere, but if after debugging (see above) you still have a problem, please fill in issues


Future
-

The original WPT Monitor has been abandoned and I maintain what my clients need, dont expect me adding lot more functionalities. 
However we tend to stick to the WPT releases, we will try to containerize the project for easier installation and we'll try to have a sitespeed.io option that will allow to have a far [better dashboard system](https://dashboard.sitespeed.io/).
There is newer projects like [OpenSpeedMonitor](https://github.com/IteraSpeed/OpenSpeedMonitor) or [WPT Charts UI](https://github.com/trulia/webpagetest-charts-ui) but none of them currrently have the number of features this project has, but maybe it's enough for your needs ?
Do not hesitate to open issues to require features, or to pull request.
