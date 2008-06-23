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
$password = ha1("Key".$uid);

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
			header( 'Location: http://www.melindasbackups.com/football/admin.php?uid='.$uid.'&pass='.$password ) ; //No CID means general. 
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
		header( 'Location: http://www.melindasbackups.com/football/admin.php?uid='.$uid.'&pass='.$password.'&cid='.$cid.'&admin=true' ) ;
	} else {
		header( 'Location: http://www.melindasbackups.com/football/nostart.html' ) ;
	};
	exit;
}	

$row = dbFetchRow($result);
if ($uid == $row['administrator']) {
	//User is administrator of this competition
	header( 'Location: http://www.melindasbackups.com/football/admin.php?uid='.$uid.'&pass='.$password.'&cid='.$cid );
	exit;
} 

//Check if this competition is open and ready yet
$result = dbQuery('SELECT * FROM Open_Competition WHERE cid = '.dbMakeSafe($cid).';');
if (dbNumRows($result) == 0 ) {
	//See if I am the adminisat
	header( 'Location: http://www.melindasbackups.com/football/nostart.html' ) ;
	exit;
}
	


if(in_array(SMF_FOOTBALL,$groups)) {
// Add Admin Link to menu section - this whole if clause has to move into menu area
};

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Melinda's Backups Chat</title>
	<link rel="stylesheet" type="text/css" href="ball.css" title="mbstyle"/>
	<!--[if lt IE 7]>
		<link rel="stylesheet" type="text/css" href="chat-ie.css"/>
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
			<h1><?php echo "This will Eventually Contain Competition Name from Database" ?></h1>
		</div>
		<!-- blank -->
		</td>
	</tr>  </tbody>
</table>

<div id="content">
<div id="copyright">MBball <span id="version"></span> &copy; 2008 Alan Chandler.  Licenced under the GPL</div>
</div>
</body>

</html>