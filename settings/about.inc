<?php 
echo '<center><h3><a href="https://github.com/WPO-Foundation/webpagetest/releases/tag/WebPageTest-'.VER_WEBPAGETEST.'">WebPageTest v '.VER_WEBPAGETEST;
echo '</a> + <a href="https://github.com/jpvincent/WPT-server">WPT Monitor Fork</a></h3></center>Agents versions are :<pre><p><b>UPDATE.INI</b></p><p>';
include ("./work/update/update.ini");
echo "</p>
<p><b>WPTUPDATE.INI</b></p><p>";
include ("./work/update/wptupdate.ini");
echo "</p></pre>";
