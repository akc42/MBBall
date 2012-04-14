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
define('DEBUG','yes'); //define debug mode to get uncompressed mootools loaded

if(!(isset($_GET['uid']) && isset($_GET['pass']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($_GET['pass'] != sha1("Football".$uid))
	die('Hacking attempt got: '.$_GET['pass'].' expected: '.sha1("Football".$uid));
if (!isset($_GET['global']) && !isset($_GET['cid']))
	die('Hacking attempt - need cid or global');

require_once('./db.inc');
require_once('./bbcode.inc');

function head_content () {
	global $uid,$password

?>	<title>Melinda's Backups Football Pool Administration</title>
	<link rel="stylesheet" type="text/css" href="calendar/calendar.css"/>
	<link rel="stylesheet" type="text/css" href="ball.css"/>
	<script src="mootools-dragmove-1.4.js" type="text/javascript" charset="UTF-8"></script>
	<script src="calendar/calendar.js" type="text/javascript" charset="UTF-8"></script>
	<script src="mbball.js" type="text/javascript" charset="UTF-8"></script>
	<script type="text/javascript">
	<!--

var MBBmgr;
window.addEvent('domready', function() {
	MBBmgr = new MBBAdmin({uid: '<?php echo $uid;?>',
				password : '<?php echo $password; ?>',
				admin :<?php if(isset($_GET['global'])) {echo 'true';} else {echo 'false';}?>},
				<?php if(isset($_GET['cid'])) {echo $_GET['cid'] ;}else{ echo '0';}?>,
                                $('errormessage')
                             );
});	

	// -->
	</script>
<?php
}
function content_title() {
	echo 'Administration Page';
}
function menu_items() {
?>		<li><a href="/forum"><span>Return to the Forum</span></a></li>
		<li><a href="/football"><span>Return to the User Page</span></a></li>
<?php
}
function content() {
?><div id="errormessage"></div>
<table class="layout">
	<tbody>
		<tr><td colspan="3"><div id="competitions"></div></td></tr>
		<tr><td colspan="2"><div id="competition"></div></td><td rowspan="3"><div id="rounds"></div></td></tr>
		<tr><td colspan="2"><div id="newround"></div></td></tr>
		<tr><td><div id="round"></div></td><td id="options"></td></tr>
		<tr><td colspan="3"><?php require_once('./emoticons.inc');?></td>
		<tr><td colspan="2" id="matches"></td><td><div id="teams"></div></td></tr>
		<tr><td colspan="3"><div id="registered"></div></td></tr>
		<tr><td colspan="3"><div id="userpick"></div></td></tr>
	</tbody>
</table>
<?php
}
function foot_content() {
?>	<div id="copyright">MBball <span><?php include('./version.inc');?></span> &copy; 2008-2011 Alan Chandler.  Licenced under the GPL</div>
<?php
}
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/template.inc'); 
?>