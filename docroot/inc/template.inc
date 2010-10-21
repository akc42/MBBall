<?php
/*
 	Copyright (c) 2010 Alan Chandler
    This file is part of Chandler's Zen Software.

    Chandler's Zen Software is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Chandler's Zen Software is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with  Chandler's Zen Software (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.

*/

/*
This template file is an example tailored for Melinda's Backups to form the site template in which to fit
applications.  This file should be placed in the 'inc' subdirectory of the document root of the site where each page
of the application (and other applications also) will call it to display the site surround, and expect it to call it
back via "head_content()", "menu_items()", "content()" and "copyright()" functions.

*/

error_reporting(E_ALL);
date_default_timezone_set  ('Europe/London');
$time_start = microtime(true);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="/css/template.css"/>
	<script src="/js/mootools-1.2.4-core-yc.js" type="text/javascript" charset="UTF-8"></script>
<?php
head_content();
?><script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-6767755-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body>
<!-- these two spans help the menu load faster-->
<span class="preload1"></span>
<span class="preload2"></span>
<div id="header"><div class="frame">
	<div id="top_section">
		<h1 class="forumlogo">
			<a href="/"><img src="/images/cz_logo.png" alt="Chandler's Zen" /></a>
		</h1>
        <div id="sitetitle"><img src="/images/football.png" alt="MBBall"/></div>
        <div id="nameContainer"><?php page_heading(); ?></div>
     </div>
</div></div>

<div id="content"><div class="frame">
    <ul class="menu">
        <li><a href="/"><span>Home</span></a></li>
<?php
menu_items();
?>    </ul>
<?php
content(); 
?>
</div></div>
<div id="footer">
	<div class="copyright"><?php copyright();?></div>
	<div class="timing"><?php printf("Page created in %.3f seconds",microtime(true)-$time_start); ?></div>
</div>
</body>
</html>

