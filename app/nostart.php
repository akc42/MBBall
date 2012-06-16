<?php
/*
 	Copyright (c) 2008-2012 Alan Chandler
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
require_once('./inc/db.inc');
$s = $db->prepare("SELECT value FROM settings WHERE name = ?");
define('MBBALL_TEMPLATE',$s->fetchSetting('template'));
unset($s);
function head_content() {
?>
	<title>Melinda's Backups Football Pool Members No Competition</title>
	<link rel="stylesheet" type="text/css" href="ball.css"/>
<?php
}
function content_title() {
	echo 'Competition Not Started Yet';
}
function menu_items() {
?>		<li><a href="/forum"><span>Return to the Forum</span></a></li>
<?php
}
function content() {
?><div id="notice">
<p>Unfortunately there are no football competitions running just yet, please check back later.</p>
</div>
<?php
}
function foot_content() {
?>	<div id="copyright">MBball <span><?php include('./inc/version.inc');?></span> &copy; 2008-2012 Alan Chandler.  Licenced under the GPL</div>
<?php
}
require_once(MBBALL_TEMPLATE); 
?>
