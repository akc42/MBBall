<?php
/*
 	Copyright (c) 2008,-2011 Alan Chandler
    This file is part of MBBall, an American Football Results Picking
    Competition Management software suite.

    MBBall is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    MBBall is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with MBBall (file supporting/COPYING.txt).  If not, 
    see <http://www.gnu.org/licenses/>.

*/
function head_content() {
?>
	<title>Melinda's Backups Football Pool Guest Page</title>
	<link rel="stylesheet" type="text/css" href="ball.css"/>
<?php
}
function content_title() {
	echo 'Guest Page';
}

function menu_items() {
?>		<li><a href="/forum"><span>Return to the Forum</span></a></li>
<?php
}
function content() {
?>	<div id="notice"><p>The Football Pool is just one of the exiting facilities available to the members of our community.</p>
		<p>To use it you have to be logged on to the forum, and at the moment you aren't.  If you are member please 
		<a href="/forum/index.php?action=login">Login First</a>.  If not, <a href="/forum/index.php?action=register">please
		consider joining</a>.</p>
	</div>
<?php
}
function foot_content() {
?>	<div id="copyright">MBball <span><?php include('./version.inc');?></span> &copy; 2008-2011 Alan Chandler.  Licenced under the GPL</div>
<?php
}
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/template.inc'); 
?>
