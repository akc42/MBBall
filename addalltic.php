<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_GET['cid'];
dbQuery('BEGIN;');
//This should give us all the teams 
dbQuery('INSERT INTO team_in_competition(cid,tid) SELECT '
	.dbMakeSafe($cid).',t.tid FROM team t LEFT JOIN team_in_competition c ON t.tid = c.tid AND cid = '
	.dbMakeSafe($cid).' WHERE c.tid IS NULL ORDER BY t.tid;');

//Now return all the teams in the competition
$result=dbQuery('SELECT tid FROM team_in_competition WHERE cid = '.dbMakeSafe($cid).' ORDER BY tid;');
echo '{"teams":[';
if($row=dbFetchRow($result)) {
	echo '"'.$row['tid'].'"';
	while($row = dbFetchRow($result)) {
		echo ',"'.$row['tid'].'"';
	}
}
dbFree($result);
dbQuery('COMMIT;');

echo ']}';
