<?php
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['desc']) && isset($_POST['adm'])))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['uid'];
$password = $_POST['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$adm=dbMakeSafe($_POST['adm']);
dbQuery('BEGIN ;');
$result=dbQuery('SELECT * FROM participant WHERE uid = '.$adm.';');
if (dbNumRows($result) == 0) {
	$adm = 'NULL';
} else {
	dbQuery('UPDATE participant SET admin_experience = TRUE WHERE uid = '.$adm.';');
}
dbFree($result);

dbQuery('INSERT INTO competition (description,administrator) VALUES ('.dbPostSafe($_POST['desc']).','.$adm.');');
$result=dbQuery('SELECT lastval() ;');
$row=dbFetchRow($result);
$lastval = $row['lastval'];
dbFree($result);
if(isset($_POST['setdefault'])) {
	dbQuery('UPDATE default_competition SET cid = '.dbMakeSafe($lastval).' ;');
} else {
	$result=dbQuery('SELECT count(*) FROM competition;');
	$row = dbFetchRow($result);
	if($row['count'] <= 1) {
		dbQuery('UPDATE default_competition SET cid = '.dbMakeSafe($lastval).' ;');
	}
}
dbQuery('COMMIT ;');

echo '{"cid":'.$lastval.'}';