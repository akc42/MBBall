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

dbQuery('INSERT INTO competition (description,administrator) VALUES ('.dbMakeSafe($_POST['desc']).','.$adm.');');
$result=dbQuery('SELECT lastval() ;');
$row=dbFetchRow($result);
if(isset($_POST['setdefault'])) dbQuery('UPDATE default_competition SET cid = '.dbMakeSafe($row['lastval']).' ;');
dbFree($result);
dbQuery('COMMIT ;');

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