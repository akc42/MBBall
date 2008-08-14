<?php
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['opid']) && isset($_GET['label']) ))
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
if (dbNumRows($result) != 0) {
	dbQuery('UPDATE OPTION SET label = '.dbPostSafe($_GET['label']).' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND opid = '.dbMakeSafe($opid).';');
	dbQuery('COMMIT ;');
	echo '{"cid":'.$cid.',"rid":'.$rid.',"opid":'.$opid.',"label":"'.$_GET['label'].'"}';
} else {
?><p>Option does not exist</p>
<?php
  dbQuery('ROLLBACK ;');
}
dbFree($result);

