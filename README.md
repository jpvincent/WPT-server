# Future and alternatives

The original WPT Monitor has been abandoned years ago and I maintain since 2012 only what my clients need : dont expect me adding lot more functionalities to this project.
Please read this [presentation that compares webperf monitoring system](https://docs.google.com/presentation/d/1bPV2fJINkQ6gKkyZaCooGEGEuk-ycEBL6_jiw5sH4mk/edit?usp=sharing) (in french, sorry).

## Open-source alternatives (DIY) :
* For easy WPT server installation, there is now an [official docker image](https://hub.docker.com/r/webpagetest/server/).
* For tests scheduling, [OpenSpeedMonitor](https://github.com/IteraSpeed/OpenSpeedMonitor) now has feature parity with this project
* For dashboards, I write custom ones for clients but you can be inspired by the best open-source webperf dashboards out there, written by the [sitespeed.IO](https://dashboard.sitespeed.io/) team (and a bit of my work)

## Paying alternatives (Saas) :
* the most known WPT paying service is [SpeedCurve](https://speedcurve.com/), the dashboards cant't be customized but the visualization is excellent
* [_contact us_](mailto:monitoring@braincracking.fr), especially if you're french : we are used to host the WPT server and agents, the scheduling and the dashboards for our own clients, at scale. Dashboards can be customized, audits can be required, we can *monitor with real devices* and we contribute back to open-source.


# Why?

This project is a fork of both [WebPageTest Private Instance 3.0](https://github.com/WPO-Foundation/webpagetest/releases/tag/WebPageTest-3.0) and [WPT Monitor](http://www.wptmonitor.org/). The agents are automatically updated since the 3.0 version. The original intention was to patch WPT monitor who looked a bit abandonned. Then, several customers of mine needed Private installs to monitor their front-end performances on a regular basis so I decided to provide a single package and give it access to anyone interested in Web Performance Monitoring.

# Reliable ?

It's used currently in three major organizations running dozen of tests per minutes on dozen of pages.
Also, I tend to follow closely the WebPageTest upgrades.

# Features

* Program WebPageTest tests (Jobs)
  * Choose locations and browsers, number of runs, first view only, video capture …
  * If you mount several [WPT agents](https://sites.google.com/a/webpagetest.org/docs/private-instances#TOC-Test-Machine-s-1), tests are made in parallel
  * select preset connectivity (tied to ```connectivity.ini```) or define one per job
  * Set Frequency of test
* Set up WebPageTest test (Scripts)
  * simple URL of course
  * [WPT Scripting](https://sites.google.com/a/webpagetest.org/docs/using-webpagetest/scripting) is supported
  * supports multi steps and HTTP basic auth
  * Data scripts, that allows to inject randomness or calculation in your tests
* Visualization
  * Basic graph for the usual metrics : TTFB, render, DOM loaded, fully loaded
  * documented placeholder to send the results to your own monitoring tool (Graphite, Splunk …) and trace all the metrics WPT gathers (speedIndex, custom metrics, CPU consumption, user metrics …)
* Alerts
  * Set alerts on metrics, with thresholds
  * set alerts on response code (404, 500 …)
  * validation mechanic : define if your test succeeds and be warned if it fails
* User management: Super Admin, regular account with number of job submission limitations
* Folders
  * organize neatly dozen of jobs and scripts
  * manage folders / users permissions
* Know tests queue state, on multiple WPT Hosts and agents
* Dated Notes, to mark on the graphes important events
* compared to the original WPT monitor :
  * more debug logs
  * clear the queue (```unlock.php```)
  * Bug fixes, like preventing ```jobProcessor_log.html``` to grow infinitely or manage a huge volume of tests without overtaking RAM

# Install

* get the code from github
* See the [https://sites.google.com/a/webpagetest.org/docs/private-instances](WPT private instance) installation documentation for requirements and first steps. The regular WPT must work, with agents running.
* have a mysql running, with a database named `wpt`
* edit `wptmonitor/settings/bootstrap.ini` for the mysql configuration
* execute `/wptmonitor/install.php` and follow [http://www.wptmonitor.org/home/installation](the old but still accurate documentation), starting from "Initial Configuration".
* setup cron jobs as per the above documentation.

## Requirements

* PHP 5.2.4 or greater (Note that WPT 3.0 works on PHP 7, but WPT Monitor has not yet been tested on it)
* following modules :
  * gcc
  * php
  * zlib
  * curl
  * php-pear
  * php-pdo
  * php-gd
  * pecl_http

# Export metrics to your own dashboard

WPT monitor is super useful to schedule tests but not that modern at displaying the collected data. You might also want to monitor additional data like the number of 404, the average size, the percentage of cached objects or the percentage of objects with gzip. Files to edit :
* `wptmonitor/custom_wpt_functions.inc` for the labelling and results exports to another monitoring tool if you need to.
* Search for the `exportResultToExternal()` calls, to enable or disable more logging
* for debug, uncomment the `echo` in the `logOutput` function, in the `wptmonitor/utils.inc`. Also simply monitor the apache error logs ("undefined index" notices are considered as normal-but-not-ideal for now)


# Support and issues

The code works on the few installations I made, so I can't guarantee it works everywhere, but if after debugging (see above) you still have a problem, please fill in issues


Do not hesitate to open issues to require features, or to pull request.
