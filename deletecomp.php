<?php
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');

dbQuery('DELETE FROM competition WHERE cid = '.dbMakeSafe($_GET['cid']).';');

echo '{"cid":'.$_GET['cid'].'}';