<?php
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_GET['cid'];
dbQuery('BEGIN ;');
$result = dbQuery('SELECT u.uid AS uuid, r.uid AS ruid FROM participant u LEFT JOIN registration r ON u.uid = r.uid AND cid = '
	.dbMakeSafe($cid).' WHERE u.uid = '.dbMakeSafe($uid).';');
$row = dbFetchRow($result);
if ($row && is_null($row['ruid'])) {
	dbQuery('INSERT INTO registration(cid,uid,agree_time) VALUES ('.dbMakeSafe($cid).','.dbMakeSafe($uid).',DEFAULT);');
	dbQuery('COMMIT ;');
	echo '{"cid":'.$cid.',"uid":"'.$uid.'"}';
} else {
	dbQuery('ROLLBACK ;');
	echo 'Error Registering. Please tell webmaster@melindasbackups.com';
}
