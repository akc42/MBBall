<?php
/*
 	Copyright (c) 2008,-2012 Alan Chandler
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

require_once('./inc/db.inc'); //This will also validate user
if (!(isset($_GET['global']) || isset($_GET['cid']))) forbidden();


$s = $db->prepare("SELECT value FROM settings WHERE name = ?");
define('MBBALL_EMOTICON_DIR',$s->fetchSetting('emoticon_dir'));
define('MBBALL_EMOTICON_URL',$s->fetchSetting('emoticon_url'));
define('MBBALL_TEMPLATE',$s->fetchSetting('template'));
$messages = Array();
$messages['deletecomp'] = $s->fetchSetting('msgdeletecomp');
$messages['deadline'] = $s->fetchSetting('msgdeadline');
$messages['nomatchdate'] = $s->fetchSetting('msgnomatchdate');
$messages['deletematch'] = $s->fetchSetting('msgdeletematch');
$messages['quesdead'] = $s->fetchSetting('msgquesdead');
$messages['nomatchround'] = $s->fetchSetting('msgnomatchround');
$messages['deleteround'] = $s->fetchSetting('msgdeleteround');
$messages['approve'] = $s->fetchSetting('msgapprove');
$messages['unregister'] = $s->fetchSetting('msgunregister');
unset($s);

function head_content () {
	global $messages

?>	<title>Melinda's Backups Football Pool Administration</title>
	<link rel="stylesheet" type="text/css" href="calendar/calendar.css"/>
	<link rel="stylesheet" type="text/css" href="ball.css"/>
<?php
	if(defined(DEBUG)) {
?>	<script src="js/mootools-dragmove-1.4.0.1.js" type="text/javascript" charset="UTF-8"></script>
<?php
	} else {
?>	<script src="js/mootools-dragmove-1.4.0.1-yc.js" type="text/javascript" charset="UTF-8"></script>
<?php
	}
?>	<script src="js/calendar/calendar.js" type="text/javascript" charset="UTF-8"></script>
	<script src="js/mbball.js" type="text/javascript" charset="UTF-8"></script>
	<script src="js/mbadmin.js" type="text/javascript" charset="UTF-8"></script>
	<script type="text/javascript">
	<!--

var MBBmgr;
window.addEvent('domready', function() {
	MBBmgr = new MBBAdmin(
						<?php if(isset($_GET['global'])) {echo 'true';} else {echo 'false';}?>,<?php if(isset($_GET['cid'])) {echo $_GET['cid'] ;}else{ echo '0';}?>,$('errormessage'),
{
<?php 
//TODO: Go through all the messages in mbbadmin and give them names to be added to the object here.  Read text from settings datatable
	$donefirst = false;
	foreach($messages as $msgid => $message){
		if($donefirst) echo ",\n";
		$dofirst = true;
		echo "$msgid:'$message'";
	}
?>
}
						);
});	

	// -->
	</script>
<?php
	unset($messages);
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
		<tr><td colspan="3"><?php require_once('./inc/emoticons.inc');?></td>
		<tr><td colspan="2" id="matches"></td><td><div id="teams"></div></td></tr>
		<tr><td colspan="3"><div id="registered"></div></td></tr>
		<tr><td colspan="3"><div id="userpick"></div></td></tr>
	</tbody>
</table>
<?php
}
function foot_content() {
?>	<div id="copyright">MBball <span><?php include('./inc/version.inc');?></span> &copy; 2008-2012 Alan Chandler.  Licenced under the GPL</div>
<?php
}
require_once(MBBALL_TEMPLATE); 
?>
