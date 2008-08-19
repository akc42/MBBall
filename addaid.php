<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['hid']) && isset($_GET['aid']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_GET['cid'];
$rid=$_GET['rid'];
$hid=$_GET['hid'];
$aid=$_GET['aid'];

dbQuery('BEGIN ;');
$result=dbQuery('SELECT * FROM match WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND hid = '.dbMakeSafe($hid).';');
$row = dbFetchRow($result);

if ($row && is_null($row['aid'])) {
  dbQuery('UPDATE  match  SET aid = '.dbMakeSafe($aid).' WHERE cid = '.dbMakeSafe($cid).' AND rid = '
                    .dbMakeSafe($rid).' AND hid = '.dbMakeSafe($hid).';');
  dbQuery('COMMIT ;');
    echo '{"cid":'.$cid.',"rid":'.$rid.',"hid":"'.$hid.'","aid":"'.$aid.'"}';
} else {
?><p>Match doesn\'t exist or Away Team already Assigned</p>
<?php
	dbQuery('ROLLBACK ;');
}
dbFree($result);

