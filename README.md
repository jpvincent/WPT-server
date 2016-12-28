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

Since most of the code is still original, your main source for help remain the [WPT forums](http://www.webpagetest.org/forums/). I mainly patched the WPT Monitor project, so in case of trouble, first chek on the [Monitor forum](http://www.webpagetest.org/forums/forumdisplay.php?fid=22), then check the original [Monitor source code](http://code.webpagetest.org/listing.php?repname=WebPagetest&path=%2Ftrunk%2Fwww%2Fwptmonitor%2F&#acd403ebe86e0515da3b1856b1c217fa1) and see if the bug you encountered could be in a file I modified myself. If so, open an issue.

Future
-

WPT Monitor is abandoned, so your hope for a better Frontend monitoring remains here. Do not hesitate to open issues to require features, or to pull request.

We are sticking to the WPT releases.


BSD License
-

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and the following
disclaimer.

* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following
disclaimer in the documentation and/or other materials provided with the distribution.

* Neither the name of the author nor the names of its contributors may be used to endorse or promote products derived
from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
