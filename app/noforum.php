<?php
/*
    Copyright (c) 2008,2009,2010 Alan Chandler
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
    along with MBBall (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.

*/

function head_content() {
?>	<style type="text/css">
		p.notice,h2 {
			width:500px;
			margin:0 auto;
			
		}
	</style>
		
<?php
}

function menu_items() {
?>		<li><a href="/forum/index.php?action=register"><span>Join Our Community</span></a></li>
		<li><a href="#"><span class="down">Melinda&#8217;s Backups&trade;</span></a>
    		<ul>
				<li><a href="/static/about.html">About Melinda&#8217;s Backups&trade;</a></li>
				<li><a href="/static/melinda.html">About Melinda</a></li>
				<li><a href="/static/charity.html">Making A Difference</a></li>
			</ul>
		</li>
<?php
}

function page_heading() {
	echo 'The Football Competition';
} 
function content() {
?>	<h2>Football</h2>
	<p class="notice">The Football Competition is just one of the exiting facilities available to the members of our community.  To
		use it you have to be logged on to the forum, and at the moment you aren't.  If you are
		member please <a href="/forum/index.php">Login First</a>.  If not, <a href="/forum/index.php?action=register">please
		consider joining</a>.</p>
<?php
}
require_once('./inc/utils.inc');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/template.inc'); 
?>

