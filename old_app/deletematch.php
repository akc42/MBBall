<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['hid']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

require_once('./db.inc');
$cid=$_GET['cid'];
$rid=$_GET['rid'];
$hid=$_GET['hid'];

dbQuery('BEGIN ;');
$result=dbQuery('SELECT * FROM match WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND hid = '.dbMakeSafe($hid).';');
if ($row=dbFetchRow($result)) {
  dbQuery('DELETE FROM match WHERE cid ='.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND hid = '.dbMakeSafe($hid).';');
  dbQuery('COMMIT ;');

    echo '{"cid":'.$cid.',"rid":'.$rid.', "hid":"'.$hid.'","aid":"'.$row['aid'].'"}';

} else {
?><p>Match doesn't exist</p>
<?php
  dbQuery('ROLLBACK ;');
}
dbFree($result);
?>
