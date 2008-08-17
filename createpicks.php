<?php
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['gap']) && isset($_POST['ppd']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['uid'];
$password = $_POST['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_POST['cid'];
$rid=$_POST['rid'];

dbQuery('BEGIN ;');
$gap = $_POST['gap'];
$ppd = $_POST['ppd'];
dbQuery('DELETE FROM option_pick WHERE 	uid = '.dbMakeSafe($uid).' AND cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).';');
dbQuery('DELETE FROM pick WHERE uid = '.dbMakeSafe($uid).' AND cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid)
		.' AND hid IN (SELECT hid FROM match WHERE  open IS TRUE and match_time > '.dbMakeSafe(time()+$gap)
		.' AND cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).');');
$opid = (isset($_POST['opid']))?$_POST['opid']:'';
$comment = (isset($_POST['Cbonus']))?$_POST['Cbonus']:'';
dbQuery('INSERT INTO option_pick(uid,cid,rid,opid,comment) VALUES ('
						.dbMakeSafe($uid).','.dbMakeSafe($cid).','.dbMakeSafe($rid).','.dbPostSafe($opid).','.dbPostSafe($comment).');');
$result=dbQuery('SELECT hid FROM match  WHERE open IS TRUE AND match_time > '.dbMakeSafe(time()+$gap).' AND cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).';');

while($row = dbFetchRow($result)) {
	$pid = (isset($_POST['M'.$row['hid']]))?$_POST['M'.$row['hid']]:'';
	$over = (isset($_POST['O'.$row['hid']]) )?(($_POST['O'.$row['hid']] == 'O')?"TRUE":"FALSE"):"NULL";
	$comment = (isset($_POST['C'.$row['hid']]))?$_POST['C'.$row['hid']]:'';
	dbQuery('INSERT INTO pick(uid,cid,rid,hid,pid,over,comment) VALUES ('
				.dbMakeSafe($uid).','.dbMakeSafe($cid).','.dbMakeSafe($rid).','.dbMakeSafe($row['hid']).','
				.dbPostSafe($pid).','.$over.','.dbPostSafe($comment).');');
}
dbFree($result);
if ($ppd != 0 && $ppd > time()) {

	require_once('confdiv.php');

	dbQuery('DELETE FROM wildcard_pick WHERE uid = '.dbMakeSafe($uid).' AND cid = '.dbMakeSafe($cid).';');
	dbQuery('DELETE FROM div_winner_pick WHERE uid = '.dbMakeSafe($uid).' AND cid = '.dbMakeSafe($cid).';');
	foreach($confs as $confid => $conference) {
		if(isset($_POST['W1'.$confid])) {
			dbQuery('INSERT INTO wildcard_pick(uid,cid,confid,opid,tid) VALUES ('
							.dbMakeSafe($uid).','.dbMakeSafe($cid).','.dbMakeSafe($confid).',1,'.dbMakeSafe($_POST['W1'.$confid]).');');
		}
		if(isset($_POST['W2'.$confid])) {
			dbQuery('INSERT INTO wildcard_pick(uid,cid,confid,opid) VALUES ('
							.dbMakeSafe($uid).','.dbMakeSafe($cid).','.dbMakeSafe($confid).',2,'.dbMakeSafe($_POST['W2'.$confid]).');');
		}
		foreach($divs as $divid => $division) {
			if (isset($_POST['D'.$divid.$confid])) {
				dbQuery('INSERT INTO div_winner_pick(uid,cid,confid,opid) VALUES ('
						.dbMakeSafe($uid).','.dbMakeSafe($cid).','.dbMakeSafe($confid).','.dbMakeSafe($divid).','.dbMakeSafe($_POST['D'.$divid.$confid]).');');
			}
		}
	}
}
dbQuery('COMMIT ;');

echo '{"uid":'.$uid.',"cid":'.$cid.',"rid":'.$rid.'}';
