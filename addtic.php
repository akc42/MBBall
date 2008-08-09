<?php
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid'])  && isset($_GET['tid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_GET['cid'];
$tid=$_GET['tid'];
dbQuery('INSERT INTO team_in_competition(cid,tid) VALUES('.dbMakeSafe($cid).','.dbMakeSafe($tid).');');


echo '{"cid":'.$cid.',"tid":"'.$tid.'"}';
