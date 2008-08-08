<?php
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
} else {
	dbQuery('ROLLBACK ;');
}
dbFree($result);


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Melinda's Backups Football Pool Create</title>
</head>
<body>
<p>Success</p>
</body>

</html>