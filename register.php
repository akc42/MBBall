<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['uid'];
$password = $_POST['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_POST['cid'];
$name=$_POST['name'];
$email=$_POST['email'];
$bb=$_POST['bb'];
dbQuery('BEGIN ;');
$result = dbQuery('SELECT * FROM participant WHERE uid = '.dbMakeSafe($uid).';');
if(dbNumRows($result) == 0) {
    dbQuery('INSERT INTO participant (uid,name,email,last_logon, is_bb) VALUES ('
.dbMakeSafe($uid).','.dbPostSafe($name).','.dbPostSafe($email).', DEFAULT,'.$bb.');');

}
dbFree($result);
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
dbFree($result);