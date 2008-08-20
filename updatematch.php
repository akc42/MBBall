<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['hid']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['uid'];
$password = $_POST['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_POST['cid'];
$rid=$_POST['rid'];
$hid=$_POST['hid'];

dbQuery('BEGIN ;');

$result=dbQuery('SELECT * FROM match WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND hid = '.dbMakeSafe($hid).';');

if (dbNumRows($result) != 0) {
	$sql = 'UPDATE match SET';
	if(isset($_POST['open'])) {
		$sql .= ' open = TRUE';
	} else {
		$sql .= ' open = FALSE';
	}
	if(isset($_POST['hscore'])) {
		$sql .= ', hscore = '.dbMakeSafe($_POST['hscore']);
	}else {
		$sql .= ', hscore = NULL';
	}
	if(isset($_POST['ascore'])) {
		$sql .= ', ascore = '.dbMakeSafe($_POST['ascore']);
	}else {
		$sql .= ', ascore = NULL';
	}
	if(isset($_POST['cscore'])) {
		$sql .= ', combined_score = '.dbMakeSafe($_POST['cscore']);
	}else {
		$sql .= ', combined_score = NULL';
	}
	if(isset($_POST['mtime']) && $_POST['mtime'] != 0) {
		$sql .= ', match_time = '.dbMakeSafe($_POST['mtime']);
	}else {
		$sql .= ', match_time = NULL';
	}
	if(isset($_POST['comment'])) {
		$sql .= ', comment = '.dbPostSafe($_POST['comment']);
	}else {
		$sql .= ', comment = NULL';
	}
	$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND hid = '.dbMakeSafe($hid).';';
	dbQuery($sql);
	dbQuery('COMMIT ;');
	echo '{"cid":'.$cid.',"rid":'.$rid.',"hid":"'.$hid.'"}';

} else {
?><p>Match does not exist</p>
<?php
	dbQuery('ROLLBACK;');
}
dbFree($result);