<?php
/* A new version of chat
	Copyright (c) 2008 Alan Chandler
	Licenced under the GPL
*/
// Link to SMF forum as this is only for logged in members
// Show all errors:
error_reporting(E_ALL);
// Path to the Ball directory:
define('MBBALL_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');

require_once(MBBALL_PATH.'../forum/SSI.php');
//If not logged in to the forum, not allowed any further so redirect to page to say so
if($user_info['is_guest']) {
	header( 'Location: http://www.melindasbackups.com/static/Football.htm' ) ;
	exit;
};

// SMF membergroup IDs for the groups that we have used to define characteristics which control Chat Group
define('SMF_FOOTBALL',		21);  //Group that can administer 
define('MBBALL_ICON_PATH', '/football/images/');

$groups =& $user_info['groups'];
$uid = $ID_MEMBER;
$name =& $user_info['name'];
$password = sha1("Key".$uid);

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');

if(isset($_GET['cid']) {
	$cid = $_GET['cid'];
} else {
	$result = dbQuery('SELECT cid FROM default_competition;');
	if(dbNumRows($result) != 0 ) {
		$row=dbFetchRow($result);
		$cid = $row['cid'];
	} else {
		if(in_array(SMF_FOOTBALL,$groups)) {
			header( 'Location: http://www.melindasbackups.com/football/admin.php?uid='.$uid.'&pass='.$password.'&global=true' ) ; 
		} else {
			header( 'Location: http://www.melindasbackups.com/football/nostart.html' ) ;
		};
		exit;
	}
	dbFree($result);	
}

$result = dbQuery('SELECT * FROM Competition WHERE cid = '.dbMakeSafe($cid).';');
if (dbNumRows($result) == 0) {
	//This competition doesn't exist yet
	if(in_array(SMF_FOOTBALL,$groups)) {
		header( 'Location: http://www.melindasbackups.com/football/admin.php?uid='.$uid.'&pass='.$password.'&global=true.'&cid='.$cid' ) ;
	} else {
		header( 'Location: http://www.melindasbackups.com/football/nostart.html' ) ;
	};
	exit;
}	
$admin = false;
$row = dbFetchRow($result);
if ($uid == $row['administrator']) {
	//User is administrator of this competition
	$admin = true;
}
dbFree($result)

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Melinda's Backups Chat</title>
	<link rel="stylesheet" type="text/css" href="ball.css" title="mbstyle"/>
	<!--[if lt IE 7]>
		<link rel="stylesheet" type="text/css" href="ball-ie.css"/>
	<![endif]-->
	<script src="/static/scripts/mootools-1.2-core.js" type="text/javascript" charset="UTF-8"></script>
	<script src="mbball.js" type="text/javascript" charset="UTF-8"></script>
</head>
<body>
<script type="text/javascript">
	<!--

window.addEvent('domready', function() {
	MBball.init({uid: <?php echo $uid;?>, 
				name: '<?php echo $name ; ?>',
				password : '<?php echo sha1("Key".$uid); ?>'});
});	
window.addEvent('unload', function() {
	MBball.logout();
	
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
			<h1><?php echo $row['description'] ?></h1>
		</div>
		<!-- blank -->
		</td>
	</tr>  </tbody>
</table>
<?php




$menu = false;
//Are we registered for the competition yet?
$result = dbQuery('SELECT * FROM registration 
			WHERE uid = '.dbMakeSafe($uid).' AND cid = '.dbMakeSafe($cid).';');
if(dbNumRows($result) == 0) {
	//We are not registered for this competition
	$registered = false;

	// Does competition allow registration at this time
	if($row['open'] == 't') {
		//yes so provide a link to register this user
?><ul id="menu">
	<li><a id="register" href="register.php?<?php echo 'uid='.$uid.'&pass='.$password.'&cid='.$cid; ?>">Register	</a></li>
<?php
		$menu=true;
	}
} else {
	$registered = true;
}
dbFree($result);
dbQuery('SELECT DISTINCT round.rid round.name FROM match JOIN round USING (cid,rid) WHERE cid = '
		.dbMakeSafe($cid).' AND status > '.(($registered)? '1' : '2').'ORDER BY rid DESC ;');
if (dbNumRows($result) > 0) {
	$row=dbFetchRow($result);

	if(isset($_GET['rid']))
		$rid=$_GET['rid'];
	} else {
		$rid=$row['rid'];  //if not set use the first row (ie highest round)
	}

	if (dbNumRows($result) >1 ) {
		// more than one round, so we need to have a menu for the others
		if (!$menu) {
?><ul id="menu">
<?php
		$menu=true;
		}
?>	<li>Rounds
		<ul>
<?php 

		do {
			if ($row['rid'] != $rid) {
?>			<li><a href="index.php?<?php echo 'cid='.$cid.'&rid='.$row['rid']; ?>"><?php echo $row['name'] ;?></a></li>
<?php
			}
		} while ($row = dbFetchRow($result));
?>		</ul>
	</li>
<?php
	}
}
dbFreeResult($result);

// The following select should select the cid and name of all competitions that are in a state where 
$sql = 'SELECT cid, name FROM competition WHERE cid <> '.dbMakeSafe($cid).' AND open IS TRUE AND cid NOT IN ';
// checking not already registered for a cid where registration is only open
$sql .= '(SELECT cid FROM registration WHERE uid = '.dbMakeSafe($uid).') UNION ';
$sql .= 'SELECT DISTINCT cid, name FROM competition JOIN match USING (cid) LEFT JOIN registration USING (cid)';
// registered users can see a competition when picks are ready for picking, non registered when awaiting results
$sql .= ' WHERE cid <> '.dbMakeSafe($cid).' AND match.status > CASE WHEN uid IS NULL THEN 2 ELSE 1 END CASE ;';
$result = dbQuery($sql);

if (dbNumRows($result) > 0) {
	if (!$menu) {
?><ul id="menu">
<?php
		$menu=true;
	}
?>	<li>Competitions
		<ul>
<?php 
	while ($row = dnFetchRow($result)) {
?>			<li><a href="index.php?<?php echo 'cid='.$row['cid'] ; ?>"><?php echo $row['name'] ;?></a></li>
<?php	}
?>		</ul>
	</li>
<?php
}
if(in_array(SMF_FOOTBALL,$groups)) {
// Am Global Administrator - let me also do Admin thinks
	if (!$menu) {
?><ul id="menu">
<?php
		$menu=true;
	}
?>	<li><a href="admin.php?<?php echo 'uid='.$uid.'&pass='.$password.'&cid='.dbMakeSafe($cid).;?>">Administration</a></li>
<?php
}
if($admin) {
// Am Administrator of this competition - let me also do Admin thinks
	if (!$menu) {
?><ul id="menu">
<?php
		$menu=true;
	}
?>	<li><a href="admin.php?<?php echo 'uid='.$uid.'&pass='.$password.'&global=true';?>">Global Admin</a></li>
<?php 
}

if ($menu) {
?></ul>
<?php
}
if ($registered) {
// If user is registered and we can do picks then we need to display the  Picks Section
?><div id="picks">
</div>
<div id="bonus">
</div>
<?php
}
<div id="copyright">MBball <span id="version"></span> &copy; 2008 Alan Chandler.  Licenced under the GPL</div>
</div>
</body>

</html>