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
	header( 'Location: /static/Football.htm' ) ;
	exit;
};

// SMF membergroup IDs for the groups that we have used to define characteristics which control Chat Group
define('SMF_FOOTBALL',		21);  //Group that can administer 
define('SMF_BABY',		10);  //Baby backup
define('MBBALL_ICON_PATH', '/football/images/');

$groups =& $user_info['groups'];
$uid = $ID_MEMBER;
$name =& $user_info['name'];
$email =& $user_infor['email'];
$password = sha1("Football".$uid);

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
if(in_array(SMF_FOOTBALL,$groups)) {
	//Global administrator - so check that participant record is up to date
	dbQuery('BEGIN;');
	$result=dbQuery('SELECT * FROM participant WHERE uid = '.dbMakeSafe($uid).';');
	if(dbNumRows($result) > 0) {
		dbQuery('UPDATE participant SET last_logon = now(), admin_experience = TRUE, name = '
				.dbMakeSafe($name).', email = '.dbMakeSafe($email).' WHERE uid = '.dbMakeSafe($uid).';');
	} else {
		dbQuery('INSERT INTO participant (uid,name,email,last_logon, admin_experience) VALUES ('
				.dbMakeSafe($uid).','.dbMakeSafe($name).','.dbMakeSafe($email).', DEFAULT,TRUE);');
	}
	dbQuery('COMMIT;');
}

if(isset($_GET['cid'])) {
	$cid = $_GET['cid'];
} else {
	$result = dbQuery('SELECT cid,version FROM default_competition;');
	if(dbNumRows($result) != 1 ) {
		die("Database is corrupt - default_competition should have a single row");
	}
	$row=dbFetchRow($result);
	$version = $row['version'];
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
	dbFree($result);	
}

$result = dbQuery('SELECT * FROM Competition WHERE cid = '.dbMakeSafe($cid).';');
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
		dbQuery('UPDATE participant SET last_logon = now(), admin_experience = TRUE, name = '
				.dbMakeSafe($name).', email = '.dbMakeSafe($email).' WHERE uid = '.dbMakeSafe($uid).';');
	} else {
		dbQuery('INSERT INTO participant (uid,name,email,last_logon, admin_experience) VALUES ('
				.dbMakeSafe($uid).','.dbMakeSafe($name).','.dbMakeSafe($email).', DEFAULT,TRUE);');
	}
	dbQuery('COMMIT;');
}
$gap = $row['gap'];   //difference from match time that picks close
$playoff_deadline=$row['pp_deadline']; //is set will be the cutoff point for playoff predictions, if 0 there is no playoff quiz
$registration_open = ($row['open'] == 't'); //is the competition open for registration
$approval_required = ($row['bb_approval'] == 't'); //BB approval is required
$condition = $row['condition'];
dbFree($result);




$result = dbQuery('SELECT * FROM registration 
			WHERE uid = '.dbMakeSafe($uid).' AND cid = '.dbMakeSafe($cid).';');
if(dbNumRows($result) <> 0) {
	$signup = true;
	$row = dbFetchRow($result);
	if ($approval_required && $row['bb_approved'] != 't' && in_array(BABY_BACKUP,$groups)) {
		$registered = false;
	} else {
		$registered = true;
	}
	if(!(in_array(SMF_FOOTBALL,$groups)  || $admin)) { //update already done if global or ordinary administrator
			//Don't touch admin experience - might not be admin now, but could have been in past
		if(in_array(BABY_BACKUP,$groups)) {
			dbQuery('UPDATE participant SET last_logon = now(), is_bb = TRUE, name = '
				.dbMakeSafe($name).', email = '.dbMakeSafe($email).' WHERE uid = '.dbMakeSafe($uid).';');
		} else {
		
			dbQuery('UPDATE participant SET last_logon = now(), is_bb = FALSE, name = '
				.dbMakeSafe($name).', email = '.dbMakeSafe($email).' WHERE uid = '.dbMakeSafe($uid).';');
		}
	}
} else {
	$signedup = false;
	$registered = false;
}
dbFree($result);

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
	MBBmgr = new MBBUser('<?php echo $version;?>',{uid: '<?php echo $uid;?>', 
				password : '<?php echo sha1("Football".$uid); ?>'});
	MBBmgr.adjustDates($('content'));
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

<ul id="menu">
	<li><a href="/forum"><span>Return to the Forum</span></a></li>
<?php
$result = dbQuery('SELECT r.rid AS rid, r.name AS name FROM round r JOIN match m USING (cid,rid) WHERE r.cid = '.dbMakeSafe($cid)
	.' AND m.open IS TRUE GROUP BY r.rid, r.name ORDER BY rid DESC ;');  //find rounds where at least one match is open
if (dbNumRows($result) > 0) {
	$row=dbFetchRow($result);
	$round_name = $row['name'];
	if(isset($_GET['rid'])) {
		$rid=$_GET['rid'];
	} else {
		$rid=$row['rid'];  //if not set use the first row (ie highest round)
	}

	if (dbNumRows($result) >1 ) {
		// more than one round, so we need to have a menu for the others
?>	<li><a href="#"><span class="down">Rounds<span><!--[if gte IE 7]><!--></a><!--<![endif]-->
		<!--[if lte IE 6]><table><tr><td><![endif]-->
		<ul>
<?php 

		do {
			if ($row['rid'] != $rid) {
?>			<li><a href="index.php?<?php echo 'cid='.$cid.'&rid='.$row['rid']; ?>"><?php echo $row['name'] ;?></a></li>
<?php
			} else {
				$round_name = $row['name'];
			}
		} while ($row = dbFetchRow($result));
?>		</ul>
		<!--[if lte IE 6]></td></tr></table></a><![endif]-->
	</li>
<?php
	}
}
dbFree($result);

// The following select should select the cid and name of all competitions that are in a state
// where there is at least one open match or it is taking registrations and we are not yet registered
$sql = 'SELECT c.cid AS cid, c.description AS name FROM competition c LEFT JOIN registration r ON c.cid = r.cid AND r.uid  = '.dbMakeSafe($uid);
$sql .= ' LEFT JOIN match m ON r.cid = m.cid WHERE c.cid <> '.dbMakeSafe($cid);
$sql .= ' AND ((r.uid IS NULL AND c.open IS TRUE ) OR m.open IS TRUE) GROUP BY c.cid, c.description ORDER BY c.cid DESC ; ';
$result = dbQuery($sql);

if (dbNumRows($result) > 0) {
?>	<li><a href="#"><span class="down">Competitions<span><!--[if gte IE 7]><!--></a><!--<![endif]-->
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
	<table class="layout">
		<tbody>
			<tr>
				<td>
<?php
	// Does competition allow registration at this time
if($registration_open && !$signedup && !$admin) {
?><div id="registeruser">
	<form id="register" action="register.php">
		<input type="hidden" name="uid" value="<?php echo $uid;?>" />
		<input type="hidden" name="pass" value="<?php echo $password;?>" />
		<input type="hidden" name="cid" value="<?php echo $cid;?>" />
<!-- registration block to be floated right -->
	<h1>Register for this Competition</h2>
<?php 
	if($condition == '') {
?>		<input type="submit" value="Register" />
<?php
	} else {
?>	<p><?php echo $name ;?>, In order to enter the competition you must agree to the following condition:-</p>
	<p><?php echo $condition;?></p>
		<input type="submit" value="I Agree" />
<?php
	}
	if (in_array(SMF_BABY,$groups) && $approval_required) {
?>	<p><sup>*</sup>Baby Backups will require special approval from the competition administrator (<?php echo $admName ;?>).
	Please contact her/him if you are a Baby Backup</p>
<?php
	}
?>	</form>
</div>
<?php		
}


//Lets get the conference and div lists as this is data useful throughout this page
$confs = array();
$divs = array(); 
$result = dbQuery('SELECT * FROM conference ORDER BY confid;');
while($row = dbFetchRow($result)) {
	$confs[$row['confid']] = $row['name'];
}
dbFree($result);
$result = dbQuery('SELECT * FROM division ORDER BY divid;');
while($row = dbFetchRow($result)) {
	$divs[$row['divid']] = $row['name'];
}
dbFree($result);

// It would be good to have a set of team names. 
$sql = 'SELECT *  FROM team_in_competition t JOIN team USING (tid)'; 
$sql .= ' WHERE t.cid = '.dbMakeSafe($cid).' ORDER BY confid,divid,tid;';
$result = dbQuery($sql);
if(dbNumRows($result) > 0 ) {
?>	<div id="team_list">
<?php
		$teams = array(array());
		$sizes = array(array());
		while ($row=dbFetchRow($result)) {
			$pick = array();
			$pick['tid']=$row['tid'];
			$pick['name']=$row['name'];
			$pick['logo']=$row['logo'];
			$pick['url']=$row['url'];
			$pick['mp'] = ($row['made_playoff'] == 't');
			$teams[$row['confid']][$row['divid']][] = $pick;
			if (isset($sizes[$row['confid']][$row['divid']])) {
				$sizes[$row['confid']][$row['divid']]++;
			} else {
				$sizes[$row['confid']][$row['divid']] = 1;
			}
		}
?>		<table>
			<caption>List of teams in competition</caption>
			<thead>
				<tr>
					<th class="po_h1">\</th><th class="po_h2">Division</th>
<?php
		foreach($divs as $division) {
			// for each division we are building a team id, name and logo columns
?>					<th class="t_dn" rowspan="2" colspan="3"><?php echo $division;?></th>
<?php
		}
?>				</tr>		
				<tr>
					<th class="po_h3">Conference</th><th class="po_h4">\</th>
				</tr>	
			</thead>
			<tbody>
<?php
		foreach($confs as $confid => $conference) {
			$no_of_rows = max($sizes[$confid]);
?>				<tr>
					<td class="po_b1" colspan="2" rowspan="<?php echo $no_of_rows;?>"><?php echo $conference;?></td>
<?php
			for ($i = 0; $i < $no_of_rows-1;$i++) {
				if( $i != 0) {
?>				<tr>
<?php
				}
				foreach($divs as $divid => $division) {
					if(isset($teams[$config][$divid][$i])) { //only if this entry is set
						$row=$teams[$config][$divid][$i];
						if($row['mp']) {
?>					<td class="in_po">
<?php
						} else {
?>					<td>
<?php						
						}
						echo $row['tid'];
?>					</td>
					<td>
<?php
						if (!is_null($row['url'])) {
						// if we have a url for team provide a link for it
							echo '<a href="'.$row['url'].'">'.$row['name'].'</a>';
						} else {
							echo $row['name'];
						}
?>					</td>
					<td>
<?php 
						if(!is_null($row['logo'])) {
							$logopath = MBBALL_ICON_PATH.$row['logo'];
							if (!is_null($row['url'])) {			
								echo '<a href="'.$row['url'].'"><img src="'.$logopath.' alt="team logo" /></a>';
							} else {
								echo '<img src="'.$logopath.' alt="team logo" />';
							}
						}
?>					</td>
<?php
					}
				}
?>				</tr>
<?php
			}
		}
?>			</tbody>
		</table>
	</div>
<?php
}
dbFree($result);
$haverounddata = false;
if(isset($rid)) {
	//We will need round data for both several things - so get it now
	$resultround = dbQuery('SELECT * FROM round WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ;');
	if(dbNumRows($resultround) !=0) {  //OK we have a round - so now we need to get it
		$rounddata = dbFetchRow($resultround);
		$haverounddata = true;
	}
	dbFree($resultround);
}		

if ($registered) {
	if($haverounddata) {  //OK we have a round - so now we need to do the picks for this round
// If user is registered and we can do picks then we need to display the  Picks Section
$sql = 'SELECT m.hid AS hid, m.aid AS aid, p.pid AS pid, m.combined_score AS cs, p.over AS over p.comment AS comment';
$sql .= ' FROM match m JOIN team t ON m.hid = t.tid LEFT JOIN pick p ';
$sql .= 'ON m.cid = p.cid AND m.rid = p.rid AND m.hid = p.hid AND p.uid = '.dbMakeSafe($uid);
$sql .= ' WHERE m.cid = '.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid).'WHERE m.open IS TRUE AND m.match_time > '.dbMakeSafe(time()+$gap);
$sql .= ' ORDER BY t.confid,t.divid, m.hid;';
$result = dbQuery($sql);
$nomatches = dbNumRows($result);
$time_at_top = time();
		if ($nomatches > 0 || $rounddata['valid_question']||
			($playoff_deadline != 0 and $play_off_deadline > $time_at_top)) {
?><form id="pick">
<?php
		}
		if($nomatches >0) {
?>	<div id="picks">
		<table>
		<caption>Match Picks for <?php echo $rounddata['name'];?></caption>
		<thead>
			<tr>
				<th class="team">Team Pick</th>
<?php
			if ($rounddata['ou_round'] == 't') {
?>				<th class="score">Score</th><th class="ou_select">Over/Under</th>
<?php
			}
?>
				<th class="comment">Comment</td>
			</tr>
		</thead>
		<tbody>
<?php
			while($row=dbFetchRow($result)) {
?>			<tr>
				<td>
					<input class="pick" type="radio" 
						name="<?php echo $row['hid'];?>"
						value="<?php echo $row['hid'];?>"
						 <?php if ($row['pid'] == $row['hid']) echo 'checked';?>/><?php echo $row['hid'];?><br/>
					<input class="pick" type="radio" 
						name="<?php echo $row['hid'];?>"
						value="<?php echo $row['aid'];?>"
						 <?php if ($row['pid'] == $row['aid']) echo 'checked';?>/><?php echo $row['aid'];?></td>
<?php
				if ($rounddata['ou_round'] == 't') {
?>				<td class="ou"><?php echo ($row['cs']+0.5);?></td><td>
					<input class="pick" type="radio"
						name="<?php echo $row['aid'];?>" value="U" 
						<?php if ($row['over'] == 'f') echo 'checked';?>/>Under<br/>
					<input class="pick" type="radio"
						name="<?php echo $row['aid'];?>" value="O" 
						<?php if ($row['over'] == 't') echo 'checked';?>/>Over<br/></td>
<?php
	}
?>				<td><textarea class="pick" rows="2" cols="20"><?php echo $row['comment'];?></textarea></td>
			</tr>
<?php
				}
			}
		
?>		</tbody>
		</table>
<?php
		}
		dbFree($result);
		if ($rounddata['valid_question']) {
?>		<table>
		<caption>Bonus Question</caption>
		<thead>
			<tr><th class="bq">Question</th><th class="ba">Answer</th><th class="comment">Comment</th></tr>
		</thead>
		<tbody>
<?php
$result=dbQuery('SELECT * FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).';');
$noopts = dbNumRows($result);  //No of multichoice examples 0= numeric answer required
$resultop = dbQuery('SELECT * FROM option_pick WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND uid = '.dbMakeSafe($uid).';');
			if (dbNumRows($resultop) > 0 ) {
				$opdata = dbFetchRow($resultop);
		}
			if ($noopts ==0 ) {
				$row = dbFetchRow($result);
//Question is a numeric type
?>			<tr><td><?php echo $rounddata['question'];?></td><td><input type="text" name="answer"
<?php
				if(!is_null($row['value'])) {
					echo 'value="'.$row['value'].'"';
				}
?>													/></td>
			<td><textarea id=bonus_comment">
<?php
				if (isset($optdata) && !is_null($opdata['comment'])) echo $opdata['comment'];
?>			</textarea></td></tr>
<?php 
			} else {
//Question is multichoice
				for($i=1; $i<$noopts;$i++) {
					$row = dbFetchRow($result);
?>			<tr>
<?php
					if($i == 1) {
?>				<td rowspan ="<?php echo $noopts ;?>"><?php echo $rounddata['question'];?></td>
<?php
					}
?>				<td><input type="radio" name="answer" value="<?php echo $row['oid'];?>"
<?php
					if(isset($opdata) && $optdata['oid'] == $row['oid']) echo 'checked';
?>					/> <?php echo $row['label']; ?></td>
<?php
					if($i == 1) {
?>				<tdrowspan ="<?php echo $noopts ;?>"><textarea id=bonus_comment">
<?php
						if (isset($optdata) && !is_null($opdata['comment'])) echo $opdata['comment'];
?>					</textarea></td>
<>php
					}
?>			</tr>
<?php
				}
			}
?>		</tbody>
		</table>
<?php
		dbFree($result);
		dbFree($resultop);
?>	</div>
<?php
		}
	} //end of check for rounddata
	if($playoff_deadline != 0 and $play_off_deadline > $time_at_top) {
//Playoff selection is part of this competition
?>	<div id="playoff">
<?php
$result=dbQuery('SELECT tid FROM div_winner_pick WHERE cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($uid).';');
		$dw = array();
		while($row = dbFetchRow($result)) {
			$dw[$row['tid']] = 1;
		}
dbFree($result);
$result=dbQuery('SELECT tid FROM wildcard_pick WHERE cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($uid).';');
		$wild = array();
		while($row = dbFetchRow($result)) {
			$wild[$row['tid']] = 1;
		}
		$wild1_shown = false;
?>		<table>
		<caption>Pick divisional winner and wildcard picks for each conference</caption>
		<thead>
			<tr>
				<th class="po_h1">\</th><th class="po_h2">Division</th>
<?php
		foreach($divs as $division) {
			// for each division we are building a team id, name and logo columns
?>				<th class="t_dn" colspan="4"><?php echo $division;?></th>
<?php
		}
?>			</tr>		
			<tr>
				<th class="po_h3">Conference</th><th class="po_h4">\</th>
<?php
		foreach($divs as $division) {
?>				<th class="po_h5">Team</th>
				<th class="po_h6">D Win</th>
				<th class="po_h6">Wild1</th>
				<th class="po_h6">Wild2</th>
<?php
		}
?>			</tr>	
		</thead>
		<tbody>
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
					if(isset($teams[$config][$divid][$i])) { //only if this entry is set
						$tid=$teams[$config][$divid][$i]['tid'];
?>				<td><?php echo $tid; ?></td>
				<td class="po_pdw">
					<input type="radio" 
						name="<?php echo $confid.$divid;?>"
						value="<?php echo $tid;?>"
						<?php if (isset($dw[$tid])) echo 'checked';?> />
				</td>
				<td class="po_wild">
					<input type="radio" 
						name="<?php echo $confid.'w1';?>"
						value="<?php echo $tid;?>"
						<?php if (isset($wild[$tid])) {echo 'checked'; $wild1_shown=true;} ?> />
				</td>
				<td class="po_wild">
					<input type="radio" 
						name="<?php echo $confid.'w2';?>"
						value="<?php echo $tid;?>"
						<?php if ($wild1_shown && isset($wild[$tid])) echo 'checked';?> />
			</td>
<?php
					}
				}
?>			</tr>
<?php
			}
		}
?>		</tbody>
		</table>
	</div>
<?php
	}  //playoffs
	if ($nomatches > 0 || $rounddata['valid_question']||
		($playoff_deadline != 0 and $play_off_deadline > $time_at_top)) {
?>	<input type="submit" name="pick_submit" value="Make Picks" />
</form>
<?php
	}
}//registered
if ($haverounddata) {
//we have a round specified, so we are definitely going to specifiy a table of everyones picks (it maybe no matches
//not matches are defined yet, but that is a different issue
?><div id="result_picks">
	<table>
		<caption>Details of this rounds pick</caption>
		<thead>
			<tr><th rowspan="4" class="match_data">Match Data</th>
<?php
$result = dbQuery('SELECT * FROM match m JOIN team t ON m.hid = t.tid WHERE m.cid = '.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid).' AND  m.open IS TRUE ORDER BY t.confid, t.divid, hid;');
$nomatches = 0;
	while($row = dbFetchRow($result)) {
		$nomatches++;
		if(!(is_null($row['hscore']) || is_null($row['ascore']) || $row['hscore'] < $row['ascore'])) {
			//Home win
			echo '<th class="win">'.$row['hid'].'</th>';
		} else {
			echo '<th>'.$row['hid'].'</th>';
		}
		if(!(is_null($row['hscore']) || is_null($row['ascore']) || $row['hscore'] > $row['ascore'])) {
			//Away win
			echo '<th class="win">'.$row['aid'].'</th>';
		} else {
			echo '<th>'.$row['aid'].'</th>';
		}
	}
	if($rounddata['valid_question'] == 't') {
//We need to find all the options so we can display them later, but for now we need to know how many for the colspan
$resultbq=dbQuery('SELECT * FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ORDER BY oid;');
$bqops = dbNumRows($resultbq);
?>				<th <?php if($bopts > 0) echo 'colspan="'.$bqops.'"';?>>Bonus Question</th>
<?php
	}
?>				<th>Points for</th>
			</tr>
			<tr>
<?php
				//first three rows first column covered by rowspan
	dbRestartQuery($result);  //put the results back to the start so we can interate over them again
	while($row = dbFetchRow($result)) {
?>				<th colspan="2"><span class="time"><?php echo $row['match_date'];?></span></th>
<?php
	}
	if($rounddata['valid_question'] == 't') {
?>				<th rowspan="2" <?php if($bopts > 0) echo 'colspan="'.$bqops.'"';?>><?php echo $rounddata['question'];?></th>
<?php
	}
?>			<th>Correct Answer</th></tr>
			<tr>
<?php
	dbRestartQuery($result);  //put the results back to the start so we can interate over them again
	while($row = dbFetchRow($result)) {
?>				<th><?php if(!is_null($row['hscore'])) echo $row['hscore'];?></th>
				<th><?php if(!is_null($row['ascore'])) echo $row['ascore'];?></th>
<?php
	}
				//column part of rowspan from previous bonus question fit in here
?>				<th><?php echo $rounddata['value'];?></th>
			</tr>
			<tr>
<?php
	dbRestartQuery($result);  //put the results back to the start so we can interate over them again
	while($row = dbFetchRow($result)) {
		if($rounddata['ou_round']) {
		//This is an over or under guessing round, so we need to also show the over/under results
			$cs = $row['combined_score']+0.5;
			if(!(is_null($row['hscore']) || is_null($row['ascore']))) {
				$scores=$row['hscore']+$row['ascore'];
?>				<th><?php echo $cs;?></th>
				<th><?php echo ($scores>$cs)?'Over':'Under';?></th>
<?php
			} else {
?>				<th><?php echo $cs;?></th><th></th>
<?php
			}
		} else {
?>				<th colspan="2"></th>
<?php		
		}
	}
	if($rounddata['valid_question'] == 't') {
		if($bqopts > 0) {
			// this is a multichoice question, so get results and output them
			while ($optdata = dbFetchRow($resultbq)) {
?>				<th <?php if(!is_null($rounddata['answer']) && $rounddata['answer'] == $optdata['oid']) echo 'class="win"';?>>
					<?php echo $optdata['label'];?></th>
<?php
			}
		} else {
?>				<th><?php if(!is_null($rounddata['answer'])) echo $rounddata['answer'];?></th>
<?php
		}
	}
?>				<th>Round Score</th>
			</tr>
		</thead>
		<tbody>
<?php
dbFree($result);
	// now we have completed the body we need to get the users resultant score
$sql = 'SELECT u.name AS name, u.uid AS uid,b.score AS rscore, r.score AS score';
$sql .= ' FROM round_score r JOIN participant u USING (uid)';
$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid);
$sql .= ' ORDER BY score DESC;';
$result = dbQuery($sql);
$sql = 'SELECT round_score r JOIN match m USING (cid,rid) JOIN team USING (hid)';
$sql .= ' LEFT JOIN pick p USING (cid,rid,hid,uid)'; 
$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND m.open IS TRUE ORDER BY r.score DESC, t.confid, t.divid, m.hid;'; 
$resultmatch = dbQuery($sql);
	if ($matchdata = dbFetch($resultmatch)) {
		$i = 0;
		while ($row = dbFetchRow($result)) {
			for($match = $i*$nomatches;$match++;$match < ($i+1)*$nomatches) {
?>			<tr>
				<td><?php echo $row['name'];?></td>
<?php
				if($rounddata['ou_round']) {
?>				<td <?php if(!is_null($matchdata[$match]['pid']) && !is_null($matchdata[$match]['hscore']) &&
						 !is_null($matchdata[$match]['ascore']) && 
						($matchdata[$match]['pid'] == $matchdata[$match]['hid'] &&
						$matchdata[$match]['hscore']>$matchdata[$match]['ascore']) ||
						($matchdata[$match]['pid'] == $matchdata[$match]['aid'] && 
						$matchdata[$match]['hscore']<$matchdata[$match]['ascore']))
							echo 'class="win"' ;?>>
					<?php if(!is_null($matchdata[$match]['pid'])) echo $matchdata[$match]['pid'];?>
				</td>
				<td <?php if(!is_null($matchdata[$match]['over']) && !is_null($matchdata[$match]['hscore']) &&
						 !is_null($matchdata[$match]['ascore']) &&
						($matchdata[$match]['over'] == 't' && 
						($matchdata[$match]['combined_score']+0.5 < $matchdata[$match]['hscore']+$matchdata[$match]['ascore'])) || 
						(!$matchdata[$match]['over'] == 't' && 
						($matchdata[$match]['combined_score']+0.5 > $matchdata[$match]['hscore']+$matchdata[$match]['ascore'])))
							echo 'class="win"';?>>
					<?php if(!is_null($matchdata[$match]['over'])) echo ($matchdata[$match]['over'] == 't')?'Over':'Under';?>
				</td>
<?php
				} else {
?>				<td colspan="2" <?php if(!is_null($matchdata[$match]['pid']) && !is_null($matchdata[$match]['hscore']) &&
						!is_null($matchdata[$match]['ascore']) && 
						($matchdata[$match]['pid'] == $matchdata[$match]['hid'] &&
						 $matchdata[$match]['hscore']>$matchdata[$match]['ascore']) ||
						($matchdata[$match]['pid'] == $matchdata[$match]['aid'] && 
						$matchdata[$match]['hscore']<$matchdata[$match]['ascore']))
							echo 'class="win"' ;?>>
					<?php if(!is_null($matchdata[$match]['pid'])) echo $matchdata[$match]['pid'];?>
				</td>
<?php
				}
				if($rounddata['valid_question'] == 't') {
					if($bqopts > 0) {
				// this is a multichoice question, so get results and output them
					dbRestartQuery($resultbq);  //reset lost of options to start
						while ($optdata = dbFetchRow($resultbq)) {
?>				<td <?php if($optdata['oid'] == $rounddata['answer'] && $row['rscore'] > 0) 
					echo 'class="win"';?>><?php if($row['rscore'] > 0) echo 'X';?></td>
<?php
						}
					} else {
?>				<td <?php if($row['rscore'] > 0) echo 'class="win"';?>>
					<?php if($row['rscore'] > 0) echo $rounddata['answer'];?></td>
<?php
					}
				}
?>				<td><?php echo $row['score']; ?></td>	
			</tr>
<?php
			}
			$i++;
		}
	}
	dbFree($result);
?>		</tbody>	
	</table>
</div>
<?php
}//have round data
if ($playoff_deadline != 0) {
?>
<div id="playoff_results">	
	<table>
		<caption>Players Playoff Picks</caption>
		<thead>
			<tr><th rowspan="3"></th>
<?php
	foreach($confs as $confid => $conference) {
?>				<th colspan="<?php echo array_sum($sizes[$confid]);?>"><?php echo $confid; ?></td>
<?php
	}
?>				<th rowspan="3">Score</th>
			</tr>
			<tr>
<?php
	foreach($confs as $confid => $conference) {
		foreach($divs as $divid => $division){
?>				<th colsspan="<?php echo $sizes[$confid][$divid];?>">
					<?php echo $divid; ?></th>
<?php
		}
	}
?>			</tr>
			<tr>
<?php
	foreach($confs as $confid => $conference) {
		foreach($divs as $divid => $division) {
			if(isset($teams[$confid][$divid])) {
				foreach($teams[$confid][$divid] as $team) {
?>				<th><?php echo $team['tid'];?></th>
<?php
				}
			}
		}
	}
?>			</tr>
		</thead>
		<tbody>
<?php
$sql = 'SELECT u.name AS name, u.uid AS uid, p.score AS score';
$sql .= ' FROM playoff_score p JOIN participant u USING (uid)';
$sql .= ' WHERE cid = '.dbMakeSafe($cid);
$sql .= ' ORDER BY score DESC;';
$result = dbQuery($sql);
	while($row = dbFetchRow($result)) {
		$playoff_selections = array();
		$resultplay = dbQuery('SELECT tid  FROM playoff_picks WHERE cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($row['uid']).';');
		while($playdata = dbFetchRow($resultplay)) {
			$playoff_selections[$playdata['tid']] = 1;
		}
?>			<tr>
				<td><?php echo $row['name']; ?></td>
<?php
		foreach($confs as $confid => $conference) {
			foreach($divs as $divid => $division){
				foreach($teams[$confid][$divid] as $team) {
?>					<td <?php if($team['mp'] && isset($playoff_selections[$team['tid']])) echo 'class="win"';?>>
						<?php if(isset($playoff_selections[$team['tid']])) echo $team['tid'];?></td>
<?php
				}
			}
		}
		dbFree($resultplay);
		unset($playoff_selections);
?>				<td><?php echo $row['score'];?></td>
			</tr>
<?php
	}
	dbFree($result);
?>		</tbody>
	</table>
</div>
<?php
}

if(isset($rid)) {
?><div id="overall_results">
	<table>
		<caption>Overall Results</caption>
		<thead>
			<tr><th>Name</th>
<?php
	$result = dbQuery('SELECT name FROM round WHERE cid = '.dbMakeSafe($cid).' AND rid <= '.dbMakeSafe($rid).' ORDER BY rid DESC;');
	while($row = dbFetchRow($result)) {
?>				<th><?php echo $row['name']; ?></th>
<?php
	}
	dbFree($result);
	if($playoff_deadline != 0) {
?>				<th>PlayOffs</th>
<?php
	}
?>				<th>Total</th>
			</tr>
		</thead>
		<tbody>
<?php
$sql = 'SELECT u.name AS name, p.score AS pscore sum(r.score) AS rscores, p.score+rscores AS total';
$sql .= ' FROM participant u JOIN registration reg USING (uid) JOIN playoff_score p USING (cid,uid) JOIN round_score USING (cid,uid)';
$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid <= '.dbMakeSafe($rid);
$sql .= ' GROUP BY u.name, p.score ORDER BY total DESC;';
	$result = dbQuery($sql);
	while($row = dbFetchRow($result)) {
?>			<tr>
				<td><?php echo $row['name'];?></td>
<?php
		$resultround = dbQuery('SELECT score FROM round_score WHERE cid = '.dbMakeSafe($cid).' AND
					 rid <= '.dbMakeSafe($rid).' AND uid = '.dbMakeSafe($uid).' ORDER BY rid DESC;');
		while ($rscore = dbFetchRow($resultround)) {
?>				<td><?php echo $row['score'];?></td>				
<?php
		}
?>			</tr>
<?php
	}
?>		</tbody>
	</table>
</div>
<?php
} else { 
?>
	<p class="notice" >Unfortunately there is no results from this competition to display right now.  Please come back later</p>
<?php
}
?>				</td>
			</tr>
		</tbody>
	</table>	
<div id="copyright"><hr />MBball <span id="version"></span> &copy; 2008 Alan Chandler.  Licenced under the GPL</div>
</div>
</body>

</html>