<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['cid']) && isset($_POST['desc']) && isset($_POST['adm'])
	&& isset($_POST['playoffdeadline']) && isset($_POST['gap']) ))
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

$sql = 'UPDATE competition SET description = '.dbPostSafe($_POST['desc']).', administrator = '.$adm.', condition = '.dbPostSafe($_POST['condition']);
$sql .= ', pp_deadline = '.dbMakeSafe($_POST['playoffdeadline']).',gap = '.dbMakeSafe($_POST['gap']*60); 
if (isset($_POST['open'])) {
	$sql .= ', open = TRUE';
} else {
	$sql .= ', open = FALSE';
}
if (isset($_POST['bbapproval'])) {
	$sql .= ', bb_approval = TRUE';
} else {
	$sql .= ', bb_approval = FALSE';
}

$sql .= ' WHERE cid = '.dbMakeSafe($_POST['cid']).' ;';
dbQuery($sql);
dbQuery('COMMIT ;');
echo '{"cid":'.$_POST['cid'].'}';
?>