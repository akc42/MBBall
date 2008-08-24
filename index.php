<?php

$version = "v1.18";

/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
// Link to SMF forum as this is only for logged in members
// Show all errors:
error_reporting(E_ALL);
// Path to the Ball directory:
define('MBBALL_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');


require_once(MBBALL_PATH.'../forum/SSI.php');
//If not logged in to the forum, not allowed any further so redirect to page to say so
if($user_info['is_guest']) {
	header( 'Location: /static/Football.htm' ) ;
	exit;
};

// SMF membergroup IDs for the groups that we have used to define characteristics which control Chat Group
define('SMF_FOOTBALL',		21);  //Group that can administer 
define('SMF_BABY',		10);  //Baby backup
define('MBBALL_ICON_PATH',	"/football/images/"); //URL where football Icons may be found
define('MBBALL_FORUM_PATH',	"/forum"); //URL to reach forum

$groups =& $user_info['groups'];
$uid = $ID_MEMBER;
$name =& $user_info['name'];
$email =& $user_info['email'];
$password = sha1("Football".$uid);

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
if(in_array(SMF_FOOTBALL,$groups)) {
	//Global administrator - so check that participant record is up to date
	dbQuery('BEGIN;');
	$result=dbQuery('SELECT * FROM participant WHERE uid = '.dbMakeSafe($uid).';');
	if(dbNumRows($result) > 0) {
		dbQuery('UPDATE participant SET last_logon = DEFAULT, admin_experience = TRUE, name = '
				.dbPostSafe($name).', email = '.dbPostSafe($email).' WHERE uid = '.dbMakeSafe($uid).';');
	} else {
		dbQuery('INSERT INTO participant (uid,name,email,last_logon, admin_experience) VALUES ('
				.dbMakeSafe($uid).','.dbPostSafe($name).','.dbPostSafe($email).', DEFAULT,TRUE);');
	}
	dbQuery('COMMIT;');
}
$result = dbQuery('SELECT cid,version FROM default_competition;');
if(dbNumRows($result) != 1 ) {
	die("<p>Database is <b>corrupt</b> - default_competition should have a single row.<br/>Please contact webmaster@melindasbackup.com</p>");
}
$row=dbFetchRow($result);
$version .= '('.$row['version'].')';

if(isset($_GET['cid'])) {
	$cid = $_GET['cid'];
} else {
	if (!is_null($row['cid'])) {
		$cid = $row['cid'];
	} else {
		if(in_array(SMF_FOOTBALL,$groups)) {
			header( 'Location: admin.php?uid='.$uid.'&pass='.$password.'&v='.$version.'&global=true' ) ; 
		} else {
			header( 'Location: nostart.html' ) ;
		};
		exit;
	}
}
dbFree($result);	

$result = dbQuery('SELECT * FROM Competition c JOIN participant u ON c.administrator = u.uid WHERE cid = '.dbMakeSafe($cid).';');
if (dbNumRows($result) == 0) {
	//This competition doesn't exist yet
	if(in_array(SMF_FOOTBALL,$groups)) {
		header( 'Location: admin.php?uid='.$uid.'&pass='.$password.'&v='.$version.'&global=true&cid='.$cid ) ;
	} else {
		header( 'Location: nostart.html' ) ;
	};
	exit;
}	
$admin = false;
$row = dbFetchRow($result);
if ($uid == $row['administrator']) {
	//User is administrator of this competition
	$admin = true;
	// check that participant record is up to date, as we are not going to be registered for the competition (maybe)
	dbQuery('BEGIN;');
	$result=dbQuery('SELECT * FROM participant WHERE uid = '.dbMakeSafe($uid).';');
	if(dbNumRows($result) > 0) {
		dbQuery('UPDATE participant SET last_logon = DEFAULT, admin_experience = TRUE, name = '
				.dbPostSafe($name).', email = '.dbPostSafe($email).' WHERE uid = '.dbMakeSafe($uid).';');
	} else {
		dbQuery('INSERT INTO participant (uid,name,email,last_logon, admin_experience) VALUES ('
				.dbMakeSafe($uid).','.dbPostSafe($name).','.dbPostSafe($email).', DEFAULT,TRUE);');
	}
	dbQuery('COMMIT;');
}
$gap = $row['gap'];   //difference from match time that picks close
$playoff_deadline=$row['pp_deadline']; //is set will be the cutoff point for playoff predictions, if 0 there is no playoff quiz
$registration_open = ($row['open'] == 't'); //is the competition open for registration
$approval_required = ($row['bb_approval'] == 't'); //BB approval is required
$condition = $row['condition'];
$admName = $row['name'];
$competitiontitle = $row['description'];
dbFree($result);




$result = dbQuery('SELECT * FROM registration 
			WHERE uid = '.dbMakeSafe($uid).' AND cid = '.dbMakeSafe($cid).';');
if(dbNumRows($result) <> 0) {
	$signedup = true;
	$row = dbFetchRow($result);
	if ($approval_required && $row['bb_approved'] != 't' && in_array(SMF_BABY,$groups)) {
		$registered = false;
	} else {
		$registered = true;
	}
	if(!(in_array(SMF_FOOTBALL,$groups)  || $admin)) { //update already done if global or ordinary administrator
			//Don't touch admin experience - might not be admin now, but could have been in past
            if(in_array(SMF_BABY,$groups)) {
			dbQuery('UPDATE participant SET last_logon = DEFAULT, is_bb = TRUE, name = '
				.dbPostSafe($name).', email = '.dbPostSafe($email).' WHERE uid = '.dbMakeSafe($uid).';');
		} else {
		
			dbQuery('UPDATE participant SET last_logon = DEFAULT, is_bb = FALSE, name = '
				.dbPostSafe($name).', email = '.dbPostSafe($email).' WHERE uid = '.dbMakeSafe($uid).';');
		}
	}
} else {
	$signedup = false;
	$registered = false;
}
$registration_allowed = ($registration_open && !$signedup && !$admin);
dbFree($result);
$result = dbQuery('SELECT * FROM round WHERE cid = '.dbMakeSafe($cid).' AND open IS TRUE ORDER BY rid DESC ;');  //find rounds where at least one match is open
if ($rounddata = dbFetchRow($result)) {
	if(isset($_GET['rid'])) {
		$rid=$rounddata['rid'];
		if ($rid != $_GET['rid']) {
			$resultround = dbQuery('SELECT * FROM round WHERE open is TRUE AND cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($_GET['rid']).' ;');
			if($possrounddata = dbFetchRow($resultround)) {
				$rounddata = $possrounddata;
				$rid = $_GET['rid'];
			}
			dbFree($resultround);			
		}
	} else {
		$rid=$rounddata['rid'];  //if not set use the first row (ie highest round)
	}
} else {
	$rid=0;
}


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Melinda's Backups Football Pool</title>
	<link rel="stylesheet" type="text/css" href="ball.css" title="mbstyle"/>
	<!--[if lt IE 7]>
		<link rel="stylesheet" type="text/css" href="ball-ie.css"/>
	<![endif]-->
	<script src="/static/scripts/mootools-1.2-core-nc.js" type="text/javascript" charset="UTF-8"></script>
	<script src="mbball.js" type="text/javascript" charset="UTF-8"></script>
</head>
<body>
<!-- these two spans seem to help the menu -->
<span class="preload1"></span>
<span class="preload2"></span>
<script type="text/javascript">
	<!--

var MBBmgr;
window.addEvent('domready', function() {
	MBBmgr = new MBBUser('<?php echo $version;?>',
				{uid: <?php echo $uid;?>, 
				password : '<?php echo sha1("Football".$uid); ?>',
				registered:<?php echo ($registered)?'true':'false';?>},
				{cid: <?php echo $cid;?>, rid: <?php echo $rid;?>},
                             $('errormessage')
	);
	MBB.adjustDates($('content'));
});	

	// -->
</script>

<table id="header" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" >
<tbody>
	<tr>
	<td align="left" width="30" class="topbg_l" height="70">&nbsp;</td>
	<td align="left" colspan="2" class="topbg_r" valign="top"><a href="/" alt="Main Site Home Page">
		<img  style="margin-top: 24px;" src="/static/images/mb-logo-community.gif" alt="Melinda's Backups Community" border="0" /></a>	
		</td>
	<td align="right" width="400" class="topbg" valign="top">
	<span style="font-family: tahoma, sans-serif; margin-left: 5px;">Melinda's Backups Community</span>
	</td>
		<td align="right" width="25" class="topbg_r2" valign="top">
		<div id="competitionNameContainer">
			<h1><?php echo $competitiontitle ?></h1>
		</div>
		<!-- blank -->
		</td>
	</tr>  </tbody>
</table>

<ul id="menu">
	<li><a href="<?php echo MBBALL_FORUM_PATH?>"><span>Return to the Forum</span></a></li>
<?php
if (dbNumRows($result) >1 ) {
	dbRestartQuery($result);
		// more than one round, so we need to have a menu for the others
?>	<li><a href="#"><span class="down">Rounds</span><!--[if gte IE 7]><!--></a><!--<![endif]-->
		<!--[if lte IE 6]><table><tr><td><![endif]-->
		<ul>
<?php 
	while ($row = dbFetchRow($result)) {
		if ($row['rid'] != $rid) {
?>			<li><a href="index.php?<?php echo 'cid='.$cid.'&rid='.$row['rid']; ?>"><?php echo $row['name'] ;?></a></li>
<?php
		}
	} ;
?>		</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
<?php
}
dbFree($result);

// The following select should select the cid and name of all competitions that are in a state
// where there is at least one open rouund or it is taking registrations and we are not yet registered
$sql = 'SELECT c.cid AS cid, c.description AS name FROM competition c LEFT JOIN registration u ON c.cid = u.cid AND u.uid  = '.dbMakeSafe($uid);
$sql .= ' LEFT JOIN round r ON c.cid = r.cid  WHERE c.cid <> '.dbMakeSafe($cid);
$sql .= ' AND ((u.uid IS NULL AND c.open IS TRUE ) OR r.open IS TRUE) GROUP BY c.cid, c.description ORDER BY c.cid DESC ; ';
$result = dbQuery($sql);

if (dbNumRows($result) > 0) {
?>	<li><a href="#"><span class="down">Competitions</span><!--[if gte IE 7]><!--></a><!--<![endif]-->
		<!--[if lte IE 6]><table><tr><td><![endif]-->
		<ul>
<?php 
	while ($row = dbFetchRow($result)) {
?>			<li><a href="index.php?<?php echo 'cid='.$row['cid'] ; ?>"><?php echo $row['name'] ;?></a></li>
<?php	}
?>		</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->

	</li>
<?php
}
dbFree($result);
if(in_array(SMF_FOOTBALL,$groups)) {
// Am Global Administrator - let me also do Admin thinks
?>	<li><a href="admin.php?<?php echo 'uid='.$uid.'&pass='.$password.'&v='.$version.'&global=true&cid='.$cid;?>"><span>Global Admin</span></a></li>

<?php
} else {
	if($admin) {
// Am Administrator of this competition - let me also do Admin thinks
?>	<li><a href="admin.php?<?php echo 'uid='.$uid.'&pass='.$password.'&v='.$version.'&cid='.$cid;?>"><span>Administration</span></a></li>
<?php 
	}
}
?></ul>
<div id="content">
 <div id="errormessage"></div>
	<table class="layout">
		<tbody>
<?php
if($registered && $rid !=0) {
?>			<tr><td colspan="2"><div id="registered"><?php require_once('userpick.php');?></div></td></tr>
<?php
} else {
	if($signedup  & !$registered) {
?>	<tr><td colspan="2"><div id="registered"><p>Although you have registered, this competition requires that Baby Backups obtain
		admistrators approval before being allowed to enter this competition.  If you have not already done so please contact the
		the administrator,  who is: <span><?php echo $admName;?></span> </p></div></td></tr>
<?php
	}
}
if($registration_allowed) {
?>			<tr>
				<td><div id="summary"><?php require_once ('summary.php');?></div></td>
				<td id="r"><div id="registration"><?php require_once ('registration.php');?></div></td>
			</tr>
<?php
} else {
?>			<tr><td colspan="2"><div id="summary"><?php require_once ('summary.php');?></div></td></tr>
<?php
}
?>			<tr><td colspan="2"><div id="picks"><?php require_once('picks.php');?></div></td></tr>
<?php
if ($playoff_deadline != 0) {
?>			<tr><td colspan="2"><div id="popicks"><?php require_once('playoff.php');?></div></td></tr>
<?php
}
?>			<tr><td colspan="2"><div id="tics"><?php require_once('tic.php');?></div></td></tr>
		</tbody>
	</table>	
	<div id="copyright"><hr />MBball <span id="version"></span> &copy; 2008 Alan Chandler.  Licenced under the GPL</div>
</div>
</body>

</html>