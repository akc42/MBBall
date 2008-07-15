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
$gap = row['gap'];   //difference from match time that picks close
$playoff_deadline=row['pp_deadline']; //is set will be the cutoff point for playoff predictions, if null there is no playoff quiz
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
		.dbMakeSafe($cid).' AND open IS TRUE '.(($registered)? '' : ' AND match_date - '.dbMakeSafe($gap)).' ORDER BY rid DESC ;');
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
$sql .= 'SELECT DISTINCT c.cid, c.name FROM competition c JOIN match m USING (cid) LEFT JOIN registration  r ';
// registered users can see a competition when picks are ready for picking, non registered when awaiting results
$sql .= 'ON r.cid = m.cid AND r.uid = '.dbMakeSafe($uid).' WHERE c.cid <> '.dbMakeSafe($cid).' AND m.open IS TRUE ';
$sqk .= 'AND CASE WHEN uid IS NULL THEN  (m.match_time - '.dbMakeSafe($gap)') > now ELSE true END CASE ;';
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
	while ($row = dbFetchRow($result)) {
?>			<li><a href="index.php?<?php echo 'cid='.$row['cid'] ; ?>"><?php echo $row['name'] ;?></a></li>
<?php	}
?>		</ul>
	</li>
<?php
}
dbFreeResult($result);
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
<div id="content">
<?php
}
if ($registered) {
// If user is registered and we can do picks then we need to display the  Picks Section
?><div id="picks">
<?php
$sql = 'SELECT m.hid AS hid, m.aid AS aid, p.pid AS pid, m.combined_score AS cs, p.over AS over p.comment AS comment';
$sql .= ' FROM match m JOIN team t LEFT JOIN pick p ';
$sql .= 'ON m.cid = p.cid AND m.rid = p.rid AND m.hid = p.hid AND p.uid = '.dbMakeSafe($uid);
$sql .= ' WHERE m.cid = '.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid).'WHERE m.open IS TRUE AND m.match_time - '.$gap.' > now()';
$sql .= ' ORDER BY t.confid,t.divid ;';
$result = dbQuery($sql);

?>	<table>
		<caption>Match Picks</caption>
		<thead>
			<tr>
				<th class="team">Team Pick</th>
				<th class="score">Score</th><th class="ou_select">Over/Under</th>
				<th class="comment">Comment</td>
			</tr>
		</thead>
		<tbody>
<?php
if (dbNumRows($result) == 0) {
?>			<tr><td colspan="2" class="nopick">No Matches to Pick</td></tr>
<?php
} else {
	while ($row=dbFetchRow($result)) {
?>			<tr><form id="<?php echo $row['hid'];?>">
				<td>
					<input class="pick" type="radio" 
						name="<?php echo $row['hid'];?>"
						value="<?php echo $row['hid'];?>"
						 <?php if ($row['pid'] == $row['hid']) echo 'checked';?>/><?php echo $row['hid'];?><br/>
					<input class="pick" type="radio" 
						name="<?php echo $row['hid'];?>"
						value="<?php echo $row['aid'];?>"
						 <?php if ($row['pid'] == $row['aid']) echo 'checked';?>/><?php echo $row['aid'];?></td>
				<td class="ou"><?php echo $row['cs'];?></td><td>
					<input class="pick" type="radio"
						name="<?php echo $row['aid'];?>" value="U" 
						<?php if ($row['over'] == 'f') echo 'checked';?>/>Under<br/>
					<input class="pick" type="radio"
						name="<?php echo $row['aid'];?>" value="O" 
						<?php if ($row['over'] == 't') echo 'checked';?>/>Over<br/></td>
				<td><textarea class="pick" rows="2" cols="20"><?php echo $row['comment'];?></textarea></td>
			    </form>
			</tr>
<?php
	}
}
dbFreeResult($result);
?>		</tbody>
	</table>
</div>
<?php
	if(!is_null($playoff_deadline) and strtotime($play_off_deadline) > time()) {
//Playoff selection is part of this competition
?><div id="playoff">
<?php
$sql = 'SELECT t.confid AS confid, .divid AS divid, t.tid AS tid, u.team AS pid, w.wild1 AS w1, w.wild2 AS w2,d.name AS dn, c.name AS cn'; 
$sql .= ' FROM team_in_competition t JOIN conference c USING (confid) JOIN division d USING (divid)';
$sql .= ' LEFT JOIN div_winner_pick u ON t.cid = u.cid AND t.confid = u.confid AND t.divid = u.divid AND t.tid = u.team AND u.uid = '.dbMakeSafe($uid);
$sql .= ' LEFT JOIN wildcard_pick w ON t.cid = w.cid AND t.confid = w.confid AND (t.tid = w.wild1 OR t.tid = w.wild2) AND u.uid = '.dbMakeSafe($uid);
$sql .= ' WHERE t.cid = '.dbMakeSafe($cid).' ORDER BY confid,divid,tid;';
		$result=dbQuery($sql);  //get all teams and whether this user as picked them
		$playoffs = array();
		$divs = array();
		$confs = array();
		$sizes = array();
		while ($row=dbFetchRow($result)) {
			$pick = array();
			$pick['tid']=$row['tid'];
			if(!isnull($row['pid'])) {
				$pick['pid']=true;
			} else {
				$pick['pid']=false;
			}
			if(!isnull($row['w1'])) {
				$pick['w1']=true;
			} else {
				$pick['w1']=false;
			}
			if(!isnull($row['w2'])) {
				$pick['w2']=true;
			} else {
				$pick['w2']=false;
			}
			$playoffs[$row['confid'],$row['divid']][] = $pick;
			if (isset($sizes[$row['confid'],$row['divid']])) {
				$sizes[$row['confid'],$row['divid']]++;
			} else {
				$sizes[$row['confid'],$row['divid']] = 1;
			}
			$confs['confid'] = $row['cn'];
			$divs['divid'] = $row['dn'];
		}
		dbFreeResult($result);
?>	<table>
		<caption>Pick divisional winner and wildcard picks for each conference</caption>
		<thead>
			<tr>
				<th class="po_h1">\</th><th class="po_h2">Division</th>
<?php
		foreach($divs as $division) {
			// for each division we are building a division name column, and three radio button colums to hold divisional winner
			// and two wildcard pick columns
?>				<th class="po_hd" rowspan="2"><?php echo $division;?></th>
				<th class="po_dw">D</th><th class="po_w1">W</th><th class="po_w2">W</th>
<?php
		}
?>			</tr>		
			<tr>
				<th class="po_h3">Conference</th><th class="po_h4">\</th>
<?php
		foreach($divs as $division) {
?>				<th>W</th><th>1</th><th>2</th>
<?php
		}
?>			</tr>	
		</thead>
		<tbody><form id="po_form">
<?php
		foreach($confs as $confid => $conference) {
			$no_of_rows = max($sizes[$confid]);
?>			<tr>
				<td class="po_b1" colspan="2" rowspan="<?php echo $no_of_rows;?>"><?php echo $conference;?></td>
<?php
			for ($i = 0; $i < $no_of_rows-1;$i++) {
				if( $i != 0) {
?>			<tr>
<?php
				}
				foreach($divs as $divid => $division) {
					$tid=$playoffs[$config,$divid,$i,'tid'];
?>				<td class="po_dn"><?php echo $tid;?></td>
				<td class="po_pdw">
					<input type="radio" 
						name="<?php echo $confid.$divid;?>"
						value="<?php echo $tid;?>"
						<?php if ($playoffs[$confid,$divid,'pid']) echo 'checked';?> />
				</td>
				<td class="po_wild">
					<input type="radio" 
						name="<?php echo $confid.'w1';?>"
						value="<?php echo $tid;?>"
						<?php if ($playoffs[$confid,$divid,'w1']) echo 'checked';?> />
				</td>
				<td class="po_wild">
					<input type="radio" 
						name="<?php echo $confid.'w2';?>"
						value="<?php echo $tid;?>"
						<?php if ($playoffs[$confid,$divid,'w2']) echo 'checked';?> />
				</td>
<?php
				}
?>			</tr>
<?php
			}
		}
?>		</form></tbody>
	</table>
</div>
<?php
	}  //playoffs
}//registered
?>
<div id="result_gen">
	<table>
	</table>
</div>
<div id="copyright">MBball <span id="version"></span> &copy; 2008 Alan Chandler.  Licenced under the GPL</div>
</div>
</body>

</html>