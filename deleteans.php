<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['opid']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_GET['cid'];
$rid=$_GET['rid'];
$opid=$_GET['opid'];

dbQuery('BEGIN ;');
$result=dbQuery('SELECT * FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND opid = '.dbMakeSafe($opid).';');
if ($row=dbFetchRow($result)) {
  dbQuery('DELETE FROM option WHERE cid ='.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND opid = '.dbMakeSafe($opid).';');
  dbQuery('COMMIT ;');

    echo '{"cid":'.$cid.',"rid":'.$rid.', "opid":"'.$opid.'"}';

} else {
?><p>Match doesn't exist</p>
<?php
  dbQuery('ROLLBACK ;');
}
dbFree($result);

