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

define('MBBALL_DB_VERSION', 13);  //Version of the database this works with
// Show all errors:
error_reporting(E_ALL);

define('MBBALL_ICON_PATH',	"img/"); //URL where football Icons may be found
// SMF membergroup IDs for the groups that we have used to define characteristics which control Chat Group
define('SMF_FOOTBALL',		21);  //Group that can administer
define('SMF_BABY',		10);  //Baby backup
define('MBBALL_AUTH','/football/remote/jsonauth.php');  //This should contain an authorisation script
define('MBBALL_KEY','Football9Key7AID'); //Must match same ones in url above (and change for new installations) - see also inc/db.inc
define('MBBALL_CHECK','FOOTBILL'); //8 chars must match same ones in url above (and change for new installations)

$time_head = microtime(true);

function simple_encrypt($text)
{
	return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256,
			MBBALL_KEY, $text, MCRYPT_MODE_ECB,
			mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256,
					MCRYPT_MODE_ECB), MCRYPT_RAND))));
}


if(!isset($_COOKIE['MBBall'])) {
/*
 * If the cookie isn't set we need to go get authorisation from whoever is designated to do so
 */
?><!DOCTYPE html >
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script src="js/mootools-core-1.5.0-full-nocompat-yc.js" type="text/javascript" charset="UTF-8"></script>
	<script src="<?php 
/*
 * The time added in the url below serves two purposes.  One it prevents the browser caching the script
 * which would be unfortunate, since we need to ensure we get a different value each time, and two it prevents a pattern 
 * emerging for that parameter which sniffers could monitor and replicate.
 */
	echo MBBALL_AUTH."?name=".urlencode(simple_encrypt(MBBALL_CHECK.time())); 
?>" type="text/javascript" charset="UTF-8"></script>
	<noscript>This application requires that Javascript is enabled in your browser!</noscript> 
</head>
<body>Authorising ...</body>
</html>
<?php
	exit;
}	

require_once('./inc/db.inc');
if($uid == 0) forbidden(); //Extra guard against guests who having gone to football, login again
$email = $user['email'];
$guest = $user['guest'];
$name = $user['name'];
//Update participant record with this user
$sql = "UPDATE participant SET name = ?, email = ?, is_guest = ?, last_logon = strftime('%s','now')";
if ($user['admin']) {
	$sql .= ", admin_experience = 1,is_global_admin = 1 WHERE uid = ?"; 
	$sql2 = "INSERT INTO participant(uid,name,email,is_guest,last_logon,admin_experience,is_global_admin) VALUES(?,?,?,?,strftime('%s','now'),1,1)";
	$global_admin = true;
} else {
	$global_admin = false;
	$sql .= " WHERE uid = ?";
	$sql2 = "INSERT INTO participant(uid,name,email,is_guest,last_logon) VALUES (?,?,?,?,strftime('%s','now'))";
}

$db->exec("BEGIN TRANSACTION");  //The whole page will run within one transaction - so its faster
$s = $db->prepare("SELECT value FROM settings WHERE name = ?");
/*
 * Here is where we add update to the database structure when we go to a new release
 */
 $currentVersion = $s->fetchSetting("version");
 if ($currentVersion < MBBALL_DB_VERSION) {
 	unset($s);  //We need to forget about this statement whilst we alter the database
	 while ($currentVersion < MBBALL_DB_VERSION) {
echo dirname(__FILE__).'/inc/update_'.$currentVersion.'.sql';
 		if(file_exists(dirname(__FILE__).'/inc/update_'.$currentVersion.'.sql')) 
	  		$db->exec(file_get_contents(dirname(__FILE__).'/inc/update_'.$currentVersion.'.sql'));
   		$currentVersion++;
  	}
  	$s = $db->prepare("SELECT value FROM settings WHERE name = ?");
 }
 
$p = $db->prepare($sql);
$p->bindString(1,$name);
$p->bindString(2,$email);
$p->bindInt(3,$guest?1:0);
$p->bindInt(4,$uid);
$p->exec();
if($p->effected() == 0) {
	unset($p);
	//No update occured so we have to insert
	$p = $db->prepare($sql2);
	$p->bindInt(1,$uid);
	$p->bindString(2,$name);
	$p->bindString(3,$email);
	$p->bindInt(4,$guest?1:0);
	$p->exec();
}
unset($p);
unset($user);  //done with it.


define('MBBALL_MAX_ROUND_DISPLAY',$s->fetchSetting('max_round_display'));
define('MBBALL_FORUM_PATH',$s->fetchSetting('home_url'));
define('MBBALL_CACHE_AGE',$s->fetchSetting('cache_age'));
//define('MBBALL_EMOTICON_DIR',$s->fetchSetting('emoticon_dir'));
//define('MBBALL_EMOTICON_URL',$s->fetchSetting('emoticon_url'));
define('MBBALL_TEMPLATE',$s->fetchSetting('template'));
define('MBBALL_CONDITION',$s->fetchSetting('msgcondition'));
define('MBBALL_GUESTNOTE',$s->fetchSetting('msgguestnote'));
$messages = Array();
$messages['noquestion'] = $s->fetchSetting('msgnoquestion');
$messages['register'] = $s->fetchSetting('msgregister');
$dcid = $s->fetchSetting('default_competition');

if(isset($_GET['cid'])) {
	$cid = $_GET['cid'];
} else {
	$cid = $dcid;
	if ($cid == 0) {
		if($global_admin) {
			header( 'Location: admin.php?uid='.$uid.'&global=true' ) ;
		} else {
			header( 'Location: nostart.php' ) ;
		};
		$db->exec("ROLLBACK");
		exit;
	}
}
unset($s);

$c = $db->prepare("SELECT * FROM Competition c JOIN participant u ON c.administrator = u.uid WHERE cid = ?");
$c->bindInt(1,$cid);
$c->exec();
if(!$row = $c->fetchRow()) {
	//This competition doesn't exist yet
	if($global_admin) {
		header( 'Location: admin.php?uid='.$uid.'&global=true&cid='.$cid ) ;
	} else {
		header( 'Location: nostart.php' ) ;
	};
	$db->exec("ROLLBACK");
	exit;
}	
$admin = false;
if ($uid == $row['administrator']) {
	//User is administrator of this competition
	$admin = true;
	$a = $db->prepare("UPDATE participant SET admin_experience = 1 WHERE uid = ?");
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
$competitionCache = $row['results_cache'];
$competitionCacheDate = $row['cache_store_date'];
unset($c);

$r = $db->prepare("SELECT * FROM registration WHERE uid = ? AND cid = ?");
$r->bindInt(1,$uid);
$r->bindInt(2,$cid);
if($row = $r->fetchRow()) {
	$signedup = true;
	if ($approval_required && $row['approved'] != 1 && $guest && !$admin) {
		$registered = false;
	} else {
		$registered = true;
	}
} else {
	$signedup = false;
	$registered = false;
}
$registration_allowed = ($registration_open && !$signedup);
unset($r);
if (isset($_GET['rid'])) {
	$r = $db->prepare("SELECT * FROM round WHERE open = 1 AND cid = ? AND rid = ?");
	$r->bindInt(2,$_GET['rid']);
} else {
	$r = $db->prepare("SELECT * FROM round WHERE cid = ? AND open = 1 ORDER BY rid DESC LIMIT 1");  //find highest round where at least one match is open
}
$r->bindInt(1,$cid);
if ($rounddata = $r->fetchRow()) {
	$rid = $rounddata['rid'];
} else {
	$rid=0;
}
unset($r);
function page_title() {
	echo "Football Pool";
}
function head_content() {
	global $uid, $registered, $cid, $rid,$messages
?>	<link rel="stylesheet" type="text/css" href="css/ball.css"/>
<?php
if (defined('DEBUG')) {
?>	<script src="js/mootools-core-1.5.0-full-nocompat.js" type="text/javascript" charset="UTF-8"></script>
	<script src="js/mbball.js" type="text/javascript" charset="UTF-8"></script>
	<script src="js/mbuser.js" type="text/javascript" charset="UTF-8"></script>
<?php
} else {
?>	<script src="js/mootools-core-1.5.0-full-nocompat-yc.js" type="text/javascript" charset="UTF-8"></script>
	<script src="js/mbball-min-<?php include('./inc/version.inc');?>.js" type="text/javascript" charset="UTF-8"></script>
	<script src="js/mbuser-min-<?php include('./inc/version.inc');?>.js" type="text/javascript" charset="UTF-8"></script>
<?php
} 
?>	<script type="text/javascript">

	<!--

var MBBmgr;
window.addEvent('domready', function() {
	MBBmgr = new MBBUser(<?php echo ($registered)?'true':'false';?>,
				{cid: <?php echo $cid;?>, rid: <?php echo $rid;?>},
                             $('errormessage'),
{
<?php 
	$donefirst = false;
	foreach($messages as $msgid => $message){
		if($donefirst) echo ",\n";
		$donefirst = true;
		echo "$msgid:'$message'";
	}
?>
}
                             );
	MBB.adjustDates($('content'));
});	

	// -->
	</script><noscript>This application requires that Javascript is enabled in your browser!</noscript> 
<?php
	unset($messages);
}
function content_title() {
	global $competitiontitle;
	echo $competitiontitle;
}
function menu_items () {
	global $cid,$rid,$uid,$dcid,$global_admin,$admin,$db;
?>		<li><a href="/forum"><span>Return to the Forum</span></a></li>
<?php
	$maxrid = $rid;
	$r = $db->prepare("SELECT rid,name FROM round WHERE open = 1 AND cid = ? and rid <> ? ORDER BY rid DESC");
	$r->bindInt(1,$cid);
	$r->bindInt(2,$rid);
	$do_first = true;
	while($row = $r->fetchRow()) {
		if($row['rid'] > $maxrid) $maxrid = $row['rid'];
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
	define('MBBALL_MAX_RID',$maxrid);
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
	$sql .= " AND (c.open = 1 OR r.open = 1 OR c.cid = ?) GROUP BY c.cid, c.description ORDER BY c.cid DESC";

	$c = $db->prepare($sql);
	$c->bindInt(1,$uid);
	$c->bindInt(2,$cid);
	$c->bindInt(3,$dcid);
	$do_first = true;
	while($row = $c->fetchRow()){
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
		
	if($global_admin) {
// Am Global Administrator - let me also do Admin things
?>		<li><a href="admin.php?<?php echo 'uid='.$uid.'&global=true&cid='.$cid;?>"><span>Global Admin</span></a></li>

<?php
	}else {	
		if($admin) {
	// Am Administrator of this competition - let me also do Admin things
		?>		<li><a href="admin.php?<?php echo 'uid='.$uid.'&cid='.$cid;?>"><span>Administration</span></a></li>
	<?php 
		} 
	}
}

function main_content() {
	global $db,$cid,$rid,$uid,$registered,$signedup,$admName,$registration_allowed,$guest,$time_head,$rounddata,$gap,
		$playoff_deadline,$approval_required,$email,$global_admin,$name,$condition,$search,$replace,$competitionCache,$competitionCacheDate;
?><div id="errormessage"></div>
	<table class="layout">
		<tbody>
<?php

	if($registered) {
?><script type="text/javascript">
	if (typeof(ga) === "function") { 
		ga('send','pageview','/football/user/registered');
	}
</script>
			<tr><td colspan="2"><div id="registered"><?php $userPicks=''; require_once('./inc/userpick.inc');?></div></td></tr>
<?php
	} else {
?><script type="text/javascript">
	if (typeof(ga) === "function") { 
		ga('send','pageview','/football/user/unregistered');
	}

</script>
<?php
		if($signedup) {
?><script type="text/javascript">
	if (typeof(ga) === "function") { 
		ga('send','pageview','/football/user/bb-awaiting-approval');
	}

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
}	
function foot_content() {
	global $db,$time_head;
?>	<div id="copyright">MBball <span><?php include('./inc/version.inc');?></span> &copy; 2008-2012 Alan Chandler.  Licenced under the GPL</div>
	<div id="timing"><?php $time_now = microtime(true); printf("With %d queries, page displayed in %.3f secs",$db->getCounts(),$time_now - $time_head);?></div>
<?php
}
require_once($_SERVER['DOCUMENT_ROOT'].MBBALL_TEMPLATE); 
$db->exec("COMMIT");  //Time to write back any updates we actually did during the creation of the page
?>

