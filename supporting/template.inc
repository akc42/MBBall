<?php
/*
 	Copyright (c) 2008-2011 Alan Chandler
    This file is part of Melinda's Backups Web Site Applications.

    Melinda's Backups Web Site Applications is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Melinda's Backups Web Site Applications is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Melinda's Backups Web Site Applications (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.

*/

/*
 * This is an example of the template that is expected - It is not used directly, but needs to be placed at 
 * $_SERVER['DOCUMENT_ROOT']."/inc/template.inc".  Define GOOGLE ACCOUNT with the correct stuff
 */
//define('GOOGLE_ACCOUNT','UA-XXXX-Y');
/* define the value DEBUG before calling this template to get uncompressed version of mootools */

?><!DOCTYPE html >
<html>
<head>
<?php 
	if(function_exists('page_title')){
?>	<title><?php page_title(); ?></title>
<?php 
	}
?>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="css/template.css"/>
<?php
if(defined('DEBUG')) {
?><script src="js/mootools-core-1.4.5-full-nocompat.js" type="text/javascript" charset="UTF-8"></script>
<?php
} else {
?>	<script src="js/mootools-core-1.4.5-full-nocompat-yc.js" type="text/javascript" charset="UTF-8"></script>
<?php
}
if(function_exists('head_content')) head_content(); 
if(defined('GOOGLE_ACCOUNT')) {
?><!-- Google Analytics -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', GOOGLE_ACCOUNT, 'auto');
ga('send', 'pageview');

</script>
<!-- End Google Analytics -->
<?php 
}
?></head>
<?php flush(); ?>
<body>

<!-- these two spans seem to help the menu -->
<span class="preload1"></span>
<span class="preload2"></span>
<div id="header"><div class="frame">
	<div id="top_section">
		<h1 class="logo">
			<a href="index.php">
				<img src="images/mbball-logo.png" alt="MBBall Football Results Picking Competition" border="0" />
			</a>
		</h1>
		<div id="siteslogan">MBBall - Football Results Picking Competition</div>
	</div>
	<div id="competitionNameContainer">
		<h1><?php if(function_exists('content_title')) content_title(); ?></h1>
	</div>
	<ul id="menu">
<?php if(function_exists('menu_items')) menu_items(); ?>
	</ul>
</div></div> <!-- #header .frame -->
<div id="wrapper"><div class="frame">
<div id="content">
<?php main_content(); ?>
</div>
</div></div> <!-- #wrapper .frame -->
<div id="footer"><div class="frame">
<?php if(function_exists('foot_content')) foot_content(); ?>
</div></div><!-- #footer .frame -->
</body>
</html>
