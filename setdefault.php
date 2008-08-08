<?php
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['defcomps'])))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['uid'];
$password = $_POST['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');

dbQuery('UPDATE default_competition SET cid = '.dbMakeSafe($_POST['defcomps']).' ;');

echo '{"cid":'.$_POST['defcomps'].'}';
