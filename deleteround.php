<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid']) && isset($_GET['rid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
$cid=$_GET['cid'];
$rid=$_GET['rid'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
dbQuery('BEGIN;');
dbQuery('DELETE FROM round WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).';');
dbQuery('UPDATE round SET rid = rid - 1 WHERE cid = '.dbMakeSafe($cid).' AND rid > '.dbMakeSafe($rid).';');
dbQuery('COMMIT;');

echo '{"cid":'.$cid.', "rid":'.$rid.'}';
?>