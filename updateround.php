<?php
/*
 	Copyright (c) 2008,2009 Alan Chandler
    This file is part of MBBall, an American Football Results Picking
    Competition Management software suite.

    MBBall is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    MBBall is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with MBBall (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.

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
		$sql .= ', answer ='.dbPostSafe($_POST['answer']);
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
