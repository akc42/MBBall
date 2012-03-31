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

define('DATA_DIR','/home/alan/dev/football/db/');
define('PRIVATE_KEY','Football19Key');


if(!file_exists(DATA_DIR.'football.ini')) {
	header('Location: install.php');
	exit;
}

$db = new PDO('sqlite:'.DATA_DIR.'football.ini');
$result = $db->query("SELECT * FROM config LIMIT 1;");
if(!($row = $result->fetch(PDO::FETCH_ASSOC))) die("Error with football.ini");
$result->closeCursor();
/*
Here is where we would test for database versions after the next update and install
any updates that there might be.  For now there is nothing to do.
*/
$default_competition = $row['default_competition'];
$max_rounds_display = $row['max_rounds_display'];

$extn_auth = $row['extn_auth'];
unset($db);


$cid = '';
if(isset($_GET['cid'])) {
	$cid = $_GET['cid'];
} else {
	if ($default_competition != '') $cid = $default_competition;
}



if(isset($_COOKIE['MBBall'])) {
	$cook = unserialize(base64_decode($_COOKIE['MBBall']));
	if($cook['admin'] && $cook['key'] != sha1(PRIVATE_KEY.$row['admin_key'])) {
		$cook['admin'] = false;
		$cookValue = base64_encode(serialize($cook));
		setcookie('MBBall',$cookValue,pow(2,31)-1);
		$_COOKIE['MBBall'] = $cookValue;
	}
	$globalAdmin = $cook['admin'];
} else {
	$cook = Array();
	$cook['admin'] = false;
	$cook['u'] = Array();
	$cook['c'] = Array();
	$globalAdmin = false;
}


if($extn_auth != '' && !isset($_COOKIE['MBBall'])) {
	require_once($extn_auth.'SSI.php');	
	//If not logged in to the forum, not allowed any further so redirect to page to say so
	if($user_info['is_guest']) {
	header( 'Location: football.php' ) ;
		exit;
	};
	//Since we are using SMF to get info - just get what we need in case we need it
	
	// SMF membergroup IDs for the groups that we have used to define characteristics which control Chat Group
	define('SMF_FOOTBALL',		21);  //Group that can administer 
	define('SMF_BABY',		10);  //Baby backup

	if(isset($user_info['id'])) { //check if this is SMFv2
		$uid =& $user_info['id'];
	} else {
		$uid = $ID_MEMBER;
	}
	$name =& $user_info['name'];
	$email =& $user_info['email'];
	$cook['admin'] = in_array(SMF_FOOTBALL,$user_info['groups']);
	$cook['key'] = sha1(PRIVATE_KEY.$admin_key);
	$cook['u']['name'] = $user_info['name'];
	$cook['u']['email'] = $user_info['email'];
	$cook['u']['guest'] = in_array(SMF_BABY,$user_info['groups']);
	$cookValue = base64_encode(serialize($cook));
	setcookie('MBBall',$cookValue,pow(2,31)-1);
	$_COOKIE['MBBall'] = $cookValue;
	$globalAdmin = $cook['admin'];
}

require_once('./inc/db.inc');

if(!$db) {
	//No default competition is defined - so admins go to the admin page, others are told to wait
	if($cook['admin']) {
		header( 'Location: admin.php' ) ;
	} else {
		header( 'Location: nostart.php' ) ;
	};
	exit;
}

$db->beginTransaction();



$result = $db->query("SELECT * FROM competition LIMIT 1;");

if(!($competition = $result->fetch(PDO::FETCH_ASSOC))) {
	$result->closeCursor();
	$db->rollBack();
	die("Database $cid is not properly formated");
}

$result->closeCursor();

//see if we are already registered
if(isset($cook['c'][$cid])) {
	$possible_uid = $cook['c'][$cid]['uid'];
	if(($extn_auth && $uid != $possible_uid) || $cook['c'][$row['cid']]['key'] != sha1(PRIVATE_KEY.$possible_uid)) {
		// ensure we are not registered
		unset($cook['c'][$row['cid']]);
		$cookValue = base64_encode(serialize($cook));
		setcookie('MBBall',$cookValue,pow(2,31)-1);
		$_COOKIE['MBBall'] = $cookValue;
		$uid = 0;
		if ($extn_auth) {
			$db->rollBack();
			header('Location: index.php');  //Restart this page again
			exit;
		}	
	}
	if(!$extn_auth) $uid = $possible_uid;
} else {
	if ($extn_auth) {
		$cook['c'][$cid]['uid'] = $uid;
		$cook['c'][$cid]['key'] = sha1(PRIVATE_KEY.$uid);
		$cookValue = base64_encode(serialize($cook));
		setcookie('MBBall',$cookValue,pow(2,31)-1);
		$_COOKIE['MBBall'] = $cookValue;
	} else {
		$uid = 0;
	}
}

function head_content() {
$db->rollBack();

function head_content() {
	global $cid,$uid,$rid,$registered;
?>	<title>Melinda's Backups Football Pool</title>
	<link rel="stylesheet" type="text/css" href="ball.css"/>
	<script src="mbball.js" type="text/javascript" charset="UTF-8"></script>
	<script src="mbbuser.js" type="text/javascript" charset="UTF-8"></script>
	<script type="text/javascript">
		<!--

	var MBBmgr;
	window.addEvent('domready', function() {
		MBBmgr = new MBBUser({uid: <?php echo $uid;?>,
					password : '<?php echo sha1("Football".$uid); ?>',
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


function menu_items () {
	global $db,$cid,$rid,$rounddata,$competition,$uid;
	$result = $db->query('SELECT * FROM round WHERE open = 1 ORDER BY rid DESC ;');  //find rounds where at least one match is open
	$rid = 0;
	while( $possrounddata = $result->fetch(PDO::FETCH_ASSOC)) {
		if($rid == 0 ) {
			$rid = $possrounddata['rid'];
			$rounddata = $possrounddata;
?>	<li><a href="#"><span class="down">Rounds</span><!--[if gte IE 7]><!--></a><!--<![endif]-->
		<!--[if lte IE 6]><table><tr><td><![endif]-->
		<ul>
<?php 
		} 
		if(isset($_GET['rid']) && $possrounddata['rid'] == $_GET['rid']) {
			$rid = $possrounddata['rid'];
			$rounddata = $possrounddata;
		}
		if($possrounddata['rid'] != $rid || (isset($_GET['rid']) && $possrounddata['rid'] != $_GET['rid'])) {
		/*
			In the situation in which $_GET['rid'] is set, but we never find an open round matching it, I will have
			created a menu item for the round that is currently being used (ie the latest one). I don't think it
			will occur, but if it does its not the end of the world. 
		*/
?>			<li><a href="index.php?<?php echo 'cid='.$cid.'&rid='.$possrounddata['rid']; ?>"><?php echo $possrounddata['name'] ;?></a></li>
<?php
			}
	}
	$result->closeCursor();
    if($rid != 0) { //we had some menu itesm
?>		</ul>
	</li>
<?php
	}
	/*
		We are going to attach to all database files that are not named the same as our current competion. We read
		the competition data to find competitions that have at least one open round, or which are accepting registrations, but
		which we are not yet registered
	*/

	if(isset($_COOKIE['MBBall'])) $cook = unserialize(base64_decode($_COOKIE['MBBall']));


	$f = $db->prepare("ATTACH ? AS football");
	$f->bindValue(1,DATA_DIR.'football.ini');
	$f->execute();
	$f->closeCursor();
	unset($f);
	$m = $db->prepare("SELECT * FROM football.cache WHERE cid != ? ORDER BY creation_date DESC");
	$m->bindValue(1,$cid);
	$m->execute();
	$competitions = $m->fetchAll(PDO::FETCH_ASSOC);
	$m->closeCursor();
	unset($m);

	if (count($competitions) > 0) {
?>	<li><a href="#"><span class="down">Competitions</span><!--[if gte IE 7]><!--></a><!--<![endif]-->
		<!--[if lte IE 6]><table><tr><td><![endif]-->
		<ul>
<?php 
		foreach ($competitions as $row) {
			$a = $db->prepare("ATTACH ? AS comp;");
			$a->bindValue(1,DATA_DIR.$row['filename'].'.db');
			$a->execute();
			$a->closeCursor();
			unset($a);

			$result = $db->query("SELECT open FROM comp.competition LIMIT 1");

			if($result->fetchColumn() == 1 && !(isset($cook) && isset($cook['c'][$row['cid']]))) {
?>			<li><a href="index.php?<?php echo 'cid='.$row['cid'] ; ?>"><?php echo $row['description'] ;?></a></li>
<?php	
			} else {
				$result->closeCursor();
				$result = $db->query("SELECT count(*) FROM comp.round WHERE open = 1");
				if($result->fetchColumn() > 0) {
?>			<li><a href="index.php?<?php echo 'cid='.$row['cid'] ; ?>"><?php echo $row['description'] ;?></a></li>
<?php	
				}
			}
			$result->closeCursor();

			$db->exec("DETACH comp;");

		}
?>		</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->

	</li>
<?php
	}
	$db->exec("DETACH football");
	unset($competitions);
	
	if(isset($cook) && $cook['admin']) {
	// Am Global Administrator - let me also do Admin things
?>	<li><a href="admin.php?<?php echo 'uid='.$uid.'&global=true&cid='.$cid;?>"><span>Global Admin</span></a></li>
<?php
	} else {
		if($uid==$competition['administrator']) {
	// Am Administrator of this competition - let me also do Admin thinks
?>	<li><a href="admin.php?<?php echo 'uid='.$uid.'&cid='.$cid;?>"><span>Administration</span></a></li>
<?php 
		}
	}
}

function page_heading () {
	global $competition;
	echo $competition['description'];
}

function content() {
	global $db,$extn_auth,$competition,$uid,$rid,$rounddata,$max_rounds_display;

	$db->beginTransaction();
	$admin = false;
	$registered = false;
	$signedup = false;
	if($uid != 0) {
		$checkuser = $db->prepare("SELECT * FROM participant WHERE uid = ? ;");
		$checkuser->bindValue(1,$uid,PDO::PARAM_INT);
		if($user = $checkuser->fetch(PDO::FETCH_ASSOC)) {
			$updateuser = $db->prepare("UPDATE participant SET last_logon = ? WHERE uid = ? ;");
			$updateuser->bindValue(1,time(),PDO::PARAM_INT);
			$updateuser->bindValue(2,$uid,PDO::PARAM_INT);
			$updateuser->execute();
			$updateuser->closeCursor();
			unset($updateuser);
			$signedup = true;
			$admin = ($uid ==$competition['admin']);
			$registered = ($user['is_guest'] == 0 || $competition['bb_approval'] == 0 || $user['approved'] == 1); 
		} else {
			if(!$extn_auth) $uid = 0;
		}
		$checkuser->closeCursor();
		unset($checkuser);
	}
	$gap = $competition['gap'];   //difference from match time that picks close
	$playoff_deadline=$competition['pp_deadline']; //is set will be the cutoff point for playoff predictions, if 0 there is no playoff quiz
	$registration_open = ($competition['open'] == 1); //is the competition open for registration
	$approval_required = ($competition['bb_approval'] == 1 ); //Guest approval is required
	$condition = $competition['condition'];
?> <div id="errormessage"></div>
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
	if($registration_open && !$signedup) {
?>			<tr>
				<td><div id="summary"><?php require_once ('./inc/summary.inc');?></div></td>
				<td id="r"><div id="registration"><?php require_once ('./inc/registration.inc');?></div></td>
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
	$db->rollBack();

}	
function foot_content() {
	global $querycounter,$time_head,$time_db;
?>	<div id="copyright">MBball <span><?php include('./version.inc');?></span> &copy; 2008-2011 Alan Chandler.  Licenced under the GPL</div>
	<div id="timing"><?php $time_now = microtime(true); printf("With %d queries, page displayed in %.3f secs of which %.3f secs was in forum checks",$querycounter,$time_now - $time_head,$time_db-$time_head);?></div>
<?php
}
require_once('./inc/utils.inc');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/template.inc'); 
?>
