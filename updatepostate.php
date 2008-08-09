<?php
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid'])  && isset($_GET['tid']) && isset($_GET['mp'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_GET['cid'];
$tid=$_GET['tid'];
$mp=$_GET['mp'];

dbQuery('UPDATE team_in_competition SET made_playoff = '.$mp.' WHERE cid ='.dbMakeSafe($cid).' AND tid = '.dbMakeSafe($tid).';');


echo '{"cid":'.$cid.',"tid":'.$tid.',"state":'.$mp.'}';
