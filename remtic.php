<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
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
dbQuery('DELETE FROM team_in_competition WHERE cid='.dbMakeSafe($cid).' AND tid = '.dbMakeSafe($tid).';');


echo '{"cid":'.$cid.',"tid":"'.$tid.'"}';
