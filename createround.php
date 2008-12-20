<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['rname']) ))
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
$result=dbQuery('SELECT * FROM competition WHERE cid = '.dbMakeSafe($cid).';');
if (dbNumRows($result) != 0) {
	if(isset($_POST['ou'])) {
		dbQuery('INSERT INTO round(cid, rid, name,ou_round) VALUES ('.dbMakeSafe($cid).','.dbMakeSafe($rid).','
			.dbPostSafe($_POST['rname']).', TRUE );');
	} else {
		dbQuery('INSERT INTO round(cid, rid, name, ou_round) VALUES ('.dbMakeSafe($cid).','.dbMakeSafe($rid).','
			.dbPostSafe($_POST['rname']).', FALSE );');
	}
	dbQuery('COMMIT ;');
	echo '{"cid":'.$cid.',"rid":'.$rid.'}';
} else {
  echo '<p>Related Competition Does Not Exist</p>';
	dbQuery('ROLLBACK ;');
}
dbFree($result);
?>