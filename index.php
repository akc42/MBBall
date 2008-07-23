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
$gap = $row['gap'];   //difference from match time that picks close
$playoff_deadline=$row['pp_deadline']; //is set will be the cutoff point for playoff predictions, if null there is no playoff quiz
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
dbQuery('SELECT DISTINCT r.rid AS rid, r.name AS name FROM match m JOIN round r USING (cid,rid) LEFT JOIN pick p USING (cid,rid) WHERE r.cid = '
		.dbMakeSafe($cid).' AND m.open IS TRUE'.(($registered)? '' : ' AND p.uid IS NOT NULL').' ORDER BY rid DESC ;');
if (dbNumRows($result) > 0) {
	$row=dbFetchRow($result);
	$round_name = $row['name'];
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
			} else {
				$round_name = $row['name'];
			}
		} while ($row = dbFetchRow($result));
?>		</ul>
	</li>
<?php
	}
}
dbFreeResult($result);

// The following select should select the cid and name of all competitions that are in a state where 
$sql = 'SELECT DISTINCT c.cid AS cid, c.name AS name FROM competition c LEFT JOIN registration r ON c.cid = r.cid AND r.uid  = '.dbMakeSafe($uid);
$sql .= ' LEFT JOIN match m USING (cid) LEFT JOIN pick p USING (cid) WHERE cid <> '.dbMakeSafe($cid);
$sql .= ' AND ((r.uid IS NULL AND (c.open IS TRUE OR p.rid IS NOT NULL)) OR (r.uid IS NOT NULL AND m.open IS TRUE)) ; ';
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
// It would be good to have a set of team names. 
$sql = 'SELECT t.confid AS confid, t.divid AS divid, t.tid AS tid, t.made_playoff AS mp, d.name AS dn, c.name AS cn,';
$sql .= 'team.name AS name, team.logo AS logo, team.url AS url'; 
$sql .= ' FROM team_in_competition t JOIN conference c USING (confid) JOIN division d USING (divid) JOIN team USING (tid)';
$sql .= ' WHERE t.cid = '.dbMakeSafe($cid).' ORDER BY confid,divid,tid;';
$result = dbQuery($sql);
if((dbNumRows($result > 0 ) {
?>	<div id="team_list">
<?php
		$result=dbQuery($sql);  //get all teams and whether this user as picked them
		$teams = array();
		$divs = array();
		$confs = array();
		$sizes = array();
		while ($row=dbFetchRow($result)) {
			$pick = array();
			$pick['tid']=$row['tid'];
			$pick['name']=$row['name'];
			$pick['logo']=$row['logo'];
			$pick['url']=$row['url'];
			$pick['mp'] = $row['mp'];
			$teams[$row['confid'],$row['divid']][] = $pick;
			if (isset($sizes[$row['confid'],$row['divid']])) {
				$sizes[$row['confid'],$row['divid']]++;
			} else {
				$sizes[$row['confid'],$row['divid']] = 1;
			}
			$confs['confid'] = $row['cn'];
			$divs['divid'] = $row['dn'];
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
					$row=$teams[$config,$divid,$i];
					if($row['madep'] == 't') {
?>					<td class="in_po">
<?php
					} else {
?>					<td>
<?php						
					}
					echo $row['tid'];
?>					</td>
					<td>
					if (!isnull($row['url']) {
						// if we have a url for team provide a link for it
						echo '<a href="'.$row['url'].'">'.$row['name'].'</a>';
					} else {
						echo $row['name'];
					}
?>					</td>
					<td>
<?php 
					if(!isnull($row['logo'])) {
						if (!isnull($row['url']) {			
							echo '<a href="'.$row['url'].'"><img src="'.$row['logo'].' alt="team logo" /></a>';
						} else {
							echo '<img src="'.$row['logo'].' alt="team logo" />';
						}
					}
?>					</td>

<?php
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
dbFreeResult($result);
We will need round data for both several things - so get it now
$resultround = dbQuery('SELECT * FROM round WHERE rid = '.dbMakeSafe($rid).' ;');
$haverounddata = false;
if(dbNumRows($resultround !=0) {  //OK we have a round - so now we need
	$rounddata = dbFetchRow($resultround);
	$haverounddata = true;
}
dbFreeResult($resultround);
		

if ($registered) {
	$resultround = dbQuery('SELECT * FROM round WHERE rid = '.dbMakeSafe($rid).' ;');
	if($haverounddata) {  //OK we have a round - so now we need to do the picks for this round
// If user is registered and we can do picks then we need to display the  Picks Section
?><div id="picks">
<?php
$sql = 'SELECT m.hid AS hid, m.aid AS aid, p.pid AS pid, m.combined_score AS cs, p.over AS over p.comment AS comment';
$sql .= ' FROM match m ON (cid, rid) JOIN team t USING (cid,rid,hid) LEFT JOIN pick p ';
$sql .= 'ON m.cid = p.cid AND m.rid = p.rid AND m.hid = p.hid AND p.uid = '.dbMakeSafe($uid);
$sql .= ' WHERE m.cid = '.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid).'WHERE m.open IS TRUE AND m.match_time > '.dbMakeSafe(time()+$gap);
$sql .= ' ORDER BY t.confid,t.divid ;';
$result = dbQuery($sql);
$norows = dbNumRows($result);
		if ($norows == 0) {
?>	<form id="picks">
<?php
		}
?>	<table>
		<caption>Match Picks for <?php echo $round_name;?></caption>
		<thead>
			<tr>
				<th class="team">Team Pick</th>
<?php
		if ( $norows > 0) {
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
		if ($norows == 0) {
?>			<tr><td colspan="2" class="nopick">No Matches to Pick</td></tr>
<?php
		} else {
			while($row=dbFetchRow($result) {
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
		}
	dbFreeResult($result);
?>		</tbody>
	</table>
<?php
	if ($rounddata['valid_question']) {
?>	<table>
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
?>			<tr><td><?php echo rounddata['question'];?></td><td><input type="text" name="answer"
<?php
			if(!isnull($row['value']) {
				echo 'value="'.$row['value'].'"';
			}
?>													/></td>
			<td><textarea id=bonus_comment">
<?php
			if (isset($optdata) && !isnull($opdata['comment']) echo $opdata['comment'];
?>			</textarea></td></tr>
<?php 
		} else {
//Question is multichoice
			for($i=1; $i<$noopts;$i++) {
				$row = dbFetchRow($result);
?>			<tr>
<?php
				if($i == 1) {
?>				<td rowspan ="<?php echo $noopts ;?>"><?php echo rounddata['question'];?></td>
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
					if (isset($optdata) && !isnull($opdata['comment']) echo $opdata['comment'];
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
	}
?>
	<textarea id="round_comment"></textarea>
	<input type="submit" name="pick_submit" value="Make Picks" />
	</form>
</div>
<?php
	} //end of check for rounddata
	if($playoff_deadline != 0 and $play_off_deadline > time()) {
//Playoff selection is part of this competition
?><div id="playoff">
<?php
$sql = 'SELECT t.confid AS confid, t.divid AS divid, t.tid AS tid, u.team AS pid, w.wild1 AS w1, w.wild2 AS w2,d.name AS dn, c.name AS cn'; 
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
	while($row = dbFetchRow($result)) {
		if(!(isnull($row['hscore']) || isnull($row['ascore']) || $row['hscore'] < $row['ascore'])) {
			//Home win
			echo '<th class="win">'.$row['hid'].'</th>';
		} else {
			echo '<th>'.$row['hid'].'</th>';
		}
		if(!(isnull($row['hscore']) || isnull($row['ascore']) || $row['hscore'] > $row['ascore'])) {
			//Home win
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
	pg_result_seek($result,0);  //put the results back to the start so we can interate over them again
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
	pg_result_Seeq($result,0);  //put the results back to the start so we can interate over them again
	while($row = dbFetchRow($result)) {
?>				<th><?php if(!isnull($row['hscore']) echo $row['hscore'];?></th>
				<th><?php if(!isnull($row['ascore']) echo $row['ascore'];?></th>
<?php
	}
				//column part of rowspan from previous bonus question fit in here
?>				<th><?php echo $rounddata['value'];?></th>
			</tr>
			<tr>
<?php
	pg_result_seek($result,0);  //put the results back to the start so we can interate over them again
	while($row = dbFetchRow($result)) {
		if($rounddata['ou_round']) {
		//This is an over or under guessing round, so we need to also show the over/under results
			$cs = $row['combined_score']+0.5;
			if(!(isnull($row['hscore']) || isnull($row['ascore']))) {
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
?>				<th <?php if(!isnull($rounddata['answer']) && $rounddata['answer'] == $optdata['oid']) echo 'class="win"';?>>
					<?php echo $optdata['label'];?></th>
<?php
			}
		} else {
?>				<th><?php if(!isnull($rounddata['answer'])) echo $rounddata['answer'];?></th>
<?php
		}
	}
?>				<th>Round Score</th>
			</tr>
		</thead>
		<tbody>
<?php
dbFreeResult($result);
	// now we have completed the body we need to get the users picks and resultant score
$sql = 'SELECT u.name AS name, u.uid AS uid, op.oid AS oid,';
$sql .= ' (sum(COALESCE(CAST((pid = hid AND hscore > ascore) OR (pid = aid AND ascore > hscore) AS integer),0) + ';
$sql .= 'COALESCE(CAST(r.ou_round AND ((((combined_score +0.5) < (hscore +ascore)) AND over)';
$sql .= ' OR (((combined_score +0.5) < (hscore +ascore)) AND NOT over)) AS integer),0) +';
$sql .= ' COALESCE(CAST((r.valid_question AND op.oid = r.answer) AS integer),0)) * r.value AS score';
$sql .= ' FROM registered r JOIN users u USING (uid) JOIN round r USING (cid) LEFT JOIN option_pick op USING (cid,rid,uid)';
$sql .= ' JOIN option o USING (cid,rid,oid)';
$sql .= ' JOIN match m USING (cid,rid) LEFT JOIN pick p USING (cid,rid,hid,uid)';
$sql .= ' GROUP BY u.uid, u.name, op.oid, o.label, r.answer,r.value, r.ou_round, r.valid_question';
$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND m.open IS TRUE ORDER BY score DESC;';
$result = dbQuery($sql);
	while ($row = dbFetchRow($result)) {
$sql = 'SELECT * FROM match m JOIN team t ON m.hid = t.tid';
$sql .= ' LEFT JOIN pick p ON m.cid = p.cid AND m.rid = p.rid AND m.hid = p.hid AND p.uid = '.dbMakeSafe($row['uid']); 
$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND m.open IS TRUE ORDER BY t.confid, t.divid, m.hid;'; 
$resultmatch = dbQuery($sql);
?>			<tr>
				<td><?php echo $row['name'];?></td>
<?php
		while($matchdata = dbFetchRow($resultmatch)) {
			if($rounddata['ou_round']) {
?>				<td <?php if(!isnull($matchdata['pid']) && !isnull($matchdata['hscore']) && !isnull($matchdata['ascore']) && 
						($matchdata['pid'] == $matchdata['hid'] && $matchdata['hscore']>$matchdata['ascore']) ||
						($matchdata['pid'] == $matchdata['aid'] && $matchdata['hscore']<$matchdata['ascore']))
							echo 'class="win"' ;?>><?php if(!isnull($resultmatch['pid'])) echo $resultmatch['pid'];?></td>
				<td <?php if(!isnull($matchdata['over']) !isnull($matchdata['hscore']) && !isnull($matchdata['ascore']) &&
					($matchdata['over'] == 't' && ($matchdata['combined_score']+0.5 < $matchdata['hscore']+$matchdata['ascore'])) || 
					(!$matchdata['over'] == 't' && ($matchdata['combined_score']+0.5 > $matchdata['hscore']+$matchdata['ascore'])))
							echo 'class="win"';?>>
					<?php if(!isnull($matchdata['over']) echo ($matchdata['over'] == 't')?'Over':'Under';?></td>
<?php
			} else {
?>				<td colspan="2" <?php if(!isnull($matchdata['pid']) && !isnull($matchdata['hscore']) && !isnull($matchdata['ascore']) && 
						($matchdata['pid'] == $matchdata['hid'] && $matchdata['hscore']>$matchdata['ascore']) ||
						($matchdata['pid'] == $matchdata['aid'] && $matchdata['hscore']<$matchdata['ascore']))
							echo 'class="win"' ;?>><?php if(!isnull($matchdata['pid'])) echo $matchdata['pid'];?></td>
<?php
			}
		}
		if($rounddata['valid_question'] == 't') {
			if($bqopts > 0) {
				// this is a multichoice question, so get results and output them
				pg_result_seek($resultbq,0);  //reset lost of options to start
				while ($optdata = dbFetchRow($resultbq)) {
?>				<td <?php if($optdata['oid'] == $matchdata['oid'] && $row['oid'] == $rounddata['answer']) 
					echo 'class="win"';?>><?php if($optdata['oid'] == $row['oid']) echo 'X';?></td>
<?php
				}
			} else {
?>				<td <?php if($row['oid'] == $rounddata['answer']) echo 'class="win"';?>>
					<?php if(!isnull($row['oid'])) echo $row['oid'];?></td>
<?php
			}
		}
?>				<td><?php echo $row['score']; ?></td>	
			</tr>
<?php
	}
	dbFreeResult($result);
?>		</tbody>	
	</table>
</div>
<?php
}
if ($playoff_deadline != 0) {
?>	<table>
		<caption>Players Playoff Picks</caption>
		<thead>
			<tr><th rowspan="3"></th>
<?php
	foreach($confs as $confid => $conference) {
?>				<th colspan="<?php echo array_sum($sizes[$confid]);?>"><?php echo $confid; ?></td>
<?php
	}
?>			</tr>
			<tr>
<?php
	foreach($confs as $confid => $conference) {
		foreach($divs as $divid => $division{
?>				<th colsspan="<?php echo $sizes[$confid,$divid];?>"><?php echo $divid; ?></td>
<?php
		}
	}
?>			</tr>
			<tr>
<?php
	foreach($confs as $confid => $conference) {
		foreach($divs as $divid => $division{
			foreach($teams[$confid,$divid] as $team) {
?>				<th><?php echo $team['tid'];?></th>
<?php
			}
		}
	}
$sql = 'SELECT u.name AS name, r.uid AS uid, sum(CAST(t.made_playoff AS integer)) AS score'
$sql .= ' FROM registration r JOIN user USING (uid)'
$sql .= ' JOIN (( SELECT cid, tid, uid  FROM wildcard_pick UNION SELECT cid,tid,uid FROM div_winner_pick) AS pick';
$sql .= ' JOIN team_in_competition t USING (cid,tid)) USING (cid,uid)';
$sql .= ' WHERE cid = '.dbMakeSafe($cid);
$sql .= ' GROUP BY u.name, r.uid'; 
$sql .= ' ORDER BY score DESC;'
$result = dbQuery($sql);
	while($row = dbFetchRow($result)) {
		$playoff_selections = array();
		$playoff_selections[] = $row['w1'];
		$playoff_selections[] = $row['w2'];
		$resultplay = dbQuery('SELECT team FROM div_winner_pick WHERE cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($row['uid']).';');
		while($playdata = dbFetchRow($resultplay)) {
			$playoff_selections[] = $playdata['team'];
		}
?>			<tr>
				<td><?php echo $row['name']; ?></td>
<?php
	}
}
?><div id="copyright">MBball <span id="version"></span> &copy; 2008 Alan Chandler.  Licenced under the GPL</div>
</div>
</body>

</html>