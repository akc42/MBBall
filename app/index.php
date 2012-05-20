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

define('DEBUG','yes');  //Define this to get an uncompressed form of the mootools core library
// Show all errors:
error_reporting(E_ALL);
// Path to the Ball directory:
define('MBBALL_ICON_PATH',	dirname(__FILE__)."/images/"); //URL where football Icons may be found
// SMF membergroup IDs for the groups that we have used to define characteristics which control Chat Group
define('SMF_FOOTBALL',		21);  //Group that can administer
define('SMF_BABY',		10);  //Baby backup

define('PRIVATE_KEY','Football19Key'); //NOTE - keep this in step with same value in inc/db.inc


$time_head = microtime(true);

if(!isset($_COOKIE['MBBall'])) {
	require_once($_SERVER['DOCUMENT_ROOT'].'/forum/SSI.php');
	//If not logged in to the forum, not allowed any further so redirect to page to say so
	if($user_info['is_guest']) {
		header( 'Location: football.php' ) ;
		exit;
	}
	$user = Array();
	$user_data = Array();
	
	$groups =& $user_info['groups'];
	if(isset($user_info['id'])) { //check if this is SMFv2
		$user['id'] =& $user_info['id'];
	} else {
		$user['id'] = $ID_MEMBER;
	}
	$user_data['name'] =& $user_info['name'];
	$user_data['email'] =& $user_info['email'];
	$user_data['admin'] = in_array(SMF_FOOTBALL,$groups); 
	$user_data['guest'] = in_array(SMF_BABY,$groups);
	$user['data'] = $user_data;
	$user['timestamp'] = time();
    $user['key'] = sha1(PRIVATE_KEY.$user['timestamp'].$user['uid'].serialize($user['data']));
	setcookie('MBBall',serialize($user),0);  //Cookie only lasts this session, 
	unset($user);
	unset($user_data);
	unset($user_info);
	unset($groups);
}
	



$time_db = microtime(true);

require_once('./inc/db.inc');
$email = $user['data']['email'];
$guest = $user['data']['is_guest'];
$name = $user['data']['name'];
//Update participant record with this user
if ($user['data']['admin']) {
	$sql = "REPLACE INTO participant(uid,name,email,is_guest,last_logon,admin_experience,is_global_admin) VALUES(?,?,?,?,DEFAULT,1,1)";
	$global_admin = true;
} else {
	$global_admin = false;
	$sql = "REPLACE INTO participant(uid,name,email,is_guest,last_logon) VALUES (?,?,?,?,DEFAULT)";
}
$p = $db->prepare($sql);
$p->bindInt(1,$uid);
$p->bindString(2,$name);
$p->bindString(3,$email);
$p->bindBool(4,$guest);
$p->exec();
unset($p);
unset($user);  //done with it.


$c = $db->prepare("SELECT * FROM config");
$c->exec();
if(!($row = $c->fetch())) {
	die("<p>Database is <b>corrupt</b> - config should be populated.<br/>Please contact webmaster@melindasbackup.com</p>");
}

define('MBBALL_MAX_ROUND_DISPLAY',$row['max_round_display']);
define('MBBALL_FORUM_PATH',$row['home_url']);


if(isset($_GET['cid'])) {
	$cid = $_GET['cid'];
} else {
	if (!is_null($row['cid'])) {
		$cid = $row['cid'];
	} else {
		if($global_admin) {
			header( 'Location: admin.php?uid='.$uid.'&global=true' ) ;
		} else {
			header( 'Location: nostart.php' ) ;
		};
		exit;
	}
}
unset($c);

$c = $db->prepare("SELECT * FROM Competition c JOIN participant u ON c.administrator = u.uid WHERE cid = ?");
$c->bindInt(1,$cid);
$c->exec();
if(!$row = $c->fetch()) {
	//This competition doesn't exist yet
	if($global_admin) {
		header( 'Location: admin.php?uid='.$uid.'&global=true&cid='.$cid ) ;
	} else {
		header( 'Location: nostart.php' ) ;
	};
	exit;
}	
$admin = false;
if ($uid == $row['administrator']) {
	//User is administrator of this competition
	$admin = true;
	$a = $db->prepare("UPDATE participant SET admin_experience = TRUE WHERE uid = ?");
	$a->bindInt(1,$uid);
	$a->exec();	
	unset($a);
}
$gap = $row['gap'];   //difference from match time that picks close
$playoff_deadline=$row['pp_deadline']; //is set will be the cutoff point for playoff predictions, if 0 there is no playoff quiz
$registration_open = ($row['open'] == 1); //is the competition open for registration
$approval_required = ($row['guest_approval'] == 1); //BB approval is required
$condition = $row['condition'];
$admName = $row['name'];
$competitiontitle = $row['description'];
unset($c);

$r = $db->prepare("SELECT * FROM registration WHERE uid = ? AND cid = ?");
$r->bindInt(1,$uid);
$r->bindInt(2,$cid);
$r->exec();
if($row = $r->fetch()) {
	$signedup = true;
	if ($approval_required && $row['bb_approved'] != 1 && $guest) {
		$registered = false;
	} else {
		$registered = true;
	}
} else {
	$signedup = false;
	$registered = false;
}
$registration_allowed = ($registration_open && !$signedup && !$admin);
unset($r);
if (isset($_GET['rid'])) {
	$r = $db->prepare("SELECT * FROM round WHERE open = 1 AND cid = ? AND rid = ?");
	$r->bindInt(2,$_GET['rid']);
} else {
	$r = $db->prepare("SELECT * FROM round WHERE cid = ? AND open = 1 ORDER BY rid LIMIT 1 DESC");  //find highest round where at least one match is open
}
$r->bindInt(1,$cid);
$r->exec();
if ($rounddata = $r->fetch()) {
	$rid = $rounddata['rid'];
} else {
	$rid=0;
}
unset($r);

function head_content() {
	global $uid, $registered, $cid, $rid
?>	<title>Melinda's Backups Football Pool</title>
	<link rel="stylesheet" type="text/css" href="ball.css"/>
	<script src="mbball.js" type="text/javascript" charset="UTF-8"></script>
	<script type="text/javascript">
	<!--

var MBBmgr;
window.addEvent('domready', function() {
	MBBmgr = new MBBUser({uid: <?php echo $uid;?>,
				registered:<?php echo ($registered)?'true':'false';?>},
				{cid: <?php echo $cid;?>, rid: <?php echo $rid;?>},
                             $('errormessage')
	);
	MBB.adjustDates($('content'));
});	

	// -->
	</script>
<?php
}
function content_title() {
	global $competitiontitle;
	echo $competitiontitle;
}
function menu_items () {
	global $cid,$rid,$uid,$global_admin,$db;
?>		<li><a href="/forum"><span>Return to the Forum</span></a></li>
<?php
	$r = $db->prepare("SELECT rid,name FROM round WHERE open = 1 AND cid = ? and rid <> ? ORDER BY rid DESC");
	$r->bindInt(1,$cid);
	$r->bindInt(2,$rid);
	$r->exec();
	$do_first = true;
	while($row = $r->fetch()) {
		if ($do_first) {
		// more than one round, so we need to have a menu for the others
?>		<li><a href="#"><span class="down">Rounds</span><!--[if gte IE 7]><!--></a><!--<![endif]-->
		<!--[if lte IE 6]><table><tr><td><![endif]-->
			<ul>
<?php 
		}
		$do_first = false;
?>				<li><a href="index.php?<?php echo 'cid='.$cid.'&rid='.$row['rid']; ?>"><?php echo $row['name'] ;?></a></li>
<?php
	}
	if(!$do_first) {
?>			</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->
		</li>
<?php
	}
	unset($r);

	// The following select should select the cid and name of all competitions that are in a state
	// where there is at least one open rouund or it is taking registrations and we are not yet registered
	$sql = "SELECT c.cid AS cid, c.description AS name FROM competition c LEFT JOIN registration u ON c.cid = u.cid AND u.uid  = ?";
	$sql .= " LEFT JOIN round r ON c.cid = r.cid  WHERE c.cid <> ?";
	$sql .= " AND (c.open = 1 OR r.open = 1) GROUP BY c.cid, c.description ORDER BY c.cid DESC";

	$c = $db->prepare($sql);
	$c->bindInt(1,$uid);
	$c->bindInt(2,$cid);
	$c->exec();
	$do_first = true;
	while($row = $c->fetch()){
		if($do_first) {
?>		<li><a href="#"><span class="down">Competitions</span><!--[if gte IE 7]><!--></a><!--<![endif]-->
		<!--[if lte IE 6]><table><tr><td><![endif]-->
			<ul>
<?php 
		}
		$do_first = false;
?>				<li><a href="index.php?<?php echo 'cid='.$row['cid'] ; ?>"><?php echo $row['name'] ;?></a></li>
<?php	
	}
	if(!$do_first) {
?>			</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->

		</li>
<?php
	}
	unset($c);
	
	if($admin) {
		// Am Administrator of this competition - let me also do Admin things
		?>		<li><a href="admin.php?<?php echo 'uid='.$uid.'&cid='.$cid;?>"><span>Administration</span></a></li>
	<?php 
	} else {		
		if($global_admin) {
	// Am Global Administrator - let me also do Admin things
?>		<li><a href="admin.php?<?php echo 'uid='.$uid.'&global=true&cid='.$cid;?>"><span>Global Admin</span></a></li>

<?php
		}
	}
}

function content() {
	global $db,$cid,$rid,$uid,$registered,$signedup,$admName,$registration_allowed,$rounddata,$gap,$playoff_deadline,$approval_required,$email,$global_admin,$name,$condition;
?><div id="errormessage"></div>
	<table class="layout">
		<tbody>
<?php

	if($registered) {
?><script type="text/javascript">
	_gaq.push(['_trackPageview','/football/user/registered']);
</script>
			<tr><td colspan="2"><div id="registered"><?php require_once('./inc/userpick.inc');?></div></td></tr>
<?php
	} else {
?><script type="text/javascript">
	_gaq.push(['_trackPageview','/football/user/unregistered']);
</script>
<?php
		if($signedup) {
?><script type="text/javascript">
	_gaq.push(['_trackPageview','/football/user/bb-awaiting-approval']);
</script>
	<tr><td colspan="2"><div id="registered"><p>Although you have registered, this competition requires that Baby Backups obtain
		admistrators approval before being allowed to enter this competition.  If you have not already done so please contact the
		the administrator,  who is: <span><?php echo $admName;?></span> </p></div></td></tr>
<?php
		}
	}
	if($registration_allowed) {
?>			<tr>
				<td><div id="summary"><?php require_once ('./inc/summary.inc');?></div></td>
				<td id="r"><div id="registration"><?php require_once ('./registration.inc');?></div></td>
			</tr>
<?php
} else {
?>			<tr><td colspan="2"><div id="summary"><?php require_once ('./inc/summary.inc');?></div></td></tr>
<?php
}
?>			<tr><td colspan="2"><div id="picks"><?php require_once('./inc/picks.inc');?></div></td></tr>
<?php
if ($playoff_deadline != 0) {
?>			<tr><td colspan="2"><div id="popicks"><?php require_once('./inc/playoff.inc');?></div></td></tr>
<?php
}
?>			<tr><td colspan="2"><div id="tics"><?php require_once('./inc/tic.inc');?></div></td></tr>
		</tbody>
	</table>
<?php
}	
function foot_content() {
	global $db,$time_head,$time_db;
?>	<div id="copyright">MBball <span><?php include('./version.inc');?></span> &copy; 2008-2011 Alan Chandler.  Licenced under the GPL</div>
	<div id="timing"><?php $time_now = microtime(true); printf("With %d queries, page displayed in %.3f secs of which %.3f secs was in forum checks",$db->getCounts(),$time_now - $time_head,$time_db-$time_head);?></div>
<?php
}
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/template.inc'); 
?>

