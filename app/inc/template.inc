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

/* define the value DEBUG before calling this template to get uncompressed version of mootools */

?><!DOCTYPE html >
<html>
<head>
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
head_content(); ?>
<!-- Google Analytics -->
  <script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '<?php echo GOOGLE_ACCOUNT; ?>']);
    _gaq.push(['_trackPageview']);
  </script>
</head>
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
		<h1><?php content_title(); ?></h1>
	</div>
	<ul id="menu">
<?php menu_items(); ?>
	</ul>
</div></div> <!-- #header .frame -->
<div id="wrapper"><div class="frame">
<div id="content">
<?php content(); ?>
</div>
</div></div> <!-- #wrapper .frame -->
<div id="footer"><div class="frame">
<?php foot_content(); ?>
</div></div><!-- #footer .frame -->
<!-- Google Analytics -->
  <script type="text/javascript">  (function() {
    var ga = document.createElement('script');     ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:'   == document.location.protocol ? 'https://ssl'   : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
   </script>
</body>
</html>
