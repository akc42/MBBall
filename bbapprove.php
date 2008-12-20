<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid']) && isset($_GET['bbuid']) && isset($_GET['approval']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');

dbQuery('UPDATE registration SET bb_approved = '.dbMakeSafe($_GET['approval']).' WHERE cid = '.dbMakeSafe($_GET['cid']).' AND uid = '.dbMakeSafe($_GET['bbuid']).';');


echo '{"cid":'.$_GET['cid'].', "uid":'.$_GET['bbuid'].',"approve":'.$_GET['approval'].'}';
?>