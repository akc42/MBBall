<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid']) && isset($_GET['ruid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
require_once('./db.inc');
$cid=$_GET['cid'];
$ruid = $_GET['ruid'];
dbQuery('DELETE FROM registration WHERE   cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($ruid).';');

echo '{"cid":'.$cid.',"ruid":"'.$ruid.'"}';
?>
