<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['rname'])
	&& isset($_POST['deadline']) && isset($_POST['value']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['uid'];
$password = $_POST['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');


$sql = 'UPDATE round SET name = '.dbPostSafe($_POST['rname']).', value = '.dbMakeSafe($_POST['value']);
$sql .= ', deadline = '.dbMakeSafe($_POST['deadline']); 
if (isset($_POST['open'])) {
	$sql .= ', open = TRUE';
} else {
	$sql .= ', open = FALSE';
}
if (isset($_POST['ou'])) {
	$sql .= ', ou_round = TRUE';
} else {
	$sql .= ', ou_round = FALSE';
}
if (isset($_POST['validquestion'])) {
	$sql .= ', valid_question = TRUE';
	if(isset($_POST['answer'])) {
		$sql .= ', answer ='.dbMakeSafe($_POST['answer']);
	} else {
		$sql .= ', answer = NULL';
	}
} else {
	$sql .= ', valid_question = FALSE, answer = NULL';
}
if(isset($_POST['question'])) {
	$sql .= ', question = '.dbPostSafe($_POST['question']);
} else {
	$sql .= ', question = \'\'';
}

$sql .= ' WHERE cid = '.dbMakeSafe($_POST['cid']).' AND rid = '.dbMakeSafe($_POST['rid']).';';
dbQuery($sql);

echo '{"cid":'.$_POST['cid'].',"rid":'.$_POST['rid'].'}';
?>