<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
if ($_GET['pass'] != sha1("Football".$uid))
	die('Hacking attempt got: '.$_GET['pass'].' expected: '.sha1("Key".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');


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
				password : '<?php echo sha1("Football".$uid); ?>'});
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
			<h1>Administration Page</h1>
		</div>
		<!-- blank -->
		</td>
	</tr>  </tbody>
</table>
<div id="content">
<?php
/* there are going to be basically two types of content here.  

1) 	If the 'global' parameter is set, then we are the global administrator
	and are going to edit the details of the "competition" records - define
	basic parameters like who is the administrator, and which competition is
	default, and create new competitions.

2)	If the 'global' parameter is not set, when it will be local to that one
	comeptition.  In this case the functions are to edit the basic data for
	a competition and to add rounds, with optional bonus questions and matches */

if(isset($_GET['global')) {
//Global administration
?><div id="competitions">
	<table>
		<caption>Football Competitions</caption>
		<thead>
			<tr>
				<th>Title</th>
				<th>Competition Administrator</th>
				<th>Default</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
<?php
$resultusers = dbQuery('SELECT uid,name FROM participant WHERE last_logon > now() - interval \'1 year 1 month\' AND is_bb IS NOT TRUE
				ORDER BY admin_experience DESC, name;');
$userdata = dbFetch($resultusers);
$result = dbQuery('SELECT description,uid,name FROM competition c LEFT JOIN participant p ON c.administrator = p.uid ORDER BY cid DESC  ;');
	if(dbNumRows($result) > 0) {
		$resultdefault = dbQuery('SELECT cid FROM default_competition;');
		if(dbNumRows($resultdefault) != 1 ) {
			die("Database is corrupt - default_competition should have a single row");
		}
		$row=dbFetchRow($resultdefault);
		if (!is_null($row['cid'])) {
			$cid = $row['cid'];
		} else {
			$cid = 0;
		}
	}
	while($row = dbFetchRow($result)) {
?>		<form id="<?php echo 'F'.$row['cid'];?>">	
			<tr>
				<td class="change">
					<input type="text" name="<?php echo 'D'.$row['cid'];?>" value="<?php echo $row['description']; ?>" />
				</td>
				<td class="change">
					<select name="<?php echo 'U'.$row['cid'];?>">
<?php
		foreach($userdata as $user) {
?>						<option value="<?php echo $user['uid'];?>" 
								<?php if ($user['uid'] == $row['uid']) echo 'selected="selected"' ;?>>
							<?php echo $user['name'] ;?>
						</option>
<?php
		}
?>					</select>
				</td>
				<td class="change">
					<input type="radio" name="default"	value="<?php echo $row['cid'];?>" 
						<?php if($cid == $row['cid']) echo 'checked="checked"' ;?> />
				</td>
				<td></td>
			</tr>
		</form>
<?php
	}
?>		<form id="create_comp">
			<tr>
				<td><input type="text" name="Dcreate" value="" /></td>
				<td><select name="Ucreate">
<?php
	foreach($userdata as $user) {
?>					<option value="<?php echo $user['uid'];?>" 
						<?php if ($user['uid'] == $uid) echo 'selected="selected"' ;?>>
						<?php echo $user['name'] ;?></option>
<?php
	}
?>					</select></td>
				<td><input type="checkbox" name="setdefault"	value="set" checked="checked" /></td>
				<td><input type="submit" value="Create" /></td>
			</tr>
		</form>
		</tbody>
	</table>					
</div>
<?php
} else {
//Competition administration
}
?><div id="copyright">MBball <span id="version"></span> &copy; 2008 Alan Chandler.  Licenced under the GPL</div>
</div>
</body>

</html>