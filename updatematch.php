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
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['hid']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['uid'];
$password = $_POST['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_POST['cid'];
$rid=$_POST['rid'];
$hid=$_POST['hid'];

dbQuery('BEGIN ;');

$result=dbQuery('SELECT * FROM match WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND hid = '.dbMakeSafe($hid).';');

if (dbNumRows($result) != 0) {
	$sql = 'UPDATE match SET';
	if(isset($_POST['open'])) {
		$sql .= ' open = TRUE';
	} else {
		$sql .= ' open = FALSE';
	}
	if(isset($_POST['hscore'])) {
		$sql .= ', hscore = '.dbMakeSafe($_POST['hscore']);
	}else {
		$sql .= ', hscore = NULL';
	}
	if(isset($_POST['ascore'])) {
		$sql .= ', ascore = '.dbMakeSafe($_POST['ascore']);
	}else {
		$sql .= ', ascore = NULL';
	}
	if(isset($_POST['cscore'])) {
		$sql .= ', combined_score = '.dbMakeSafe($_POST['cscore']);
	}else {
		$sql .= ', combined_score = NULL';
	}
	if(isset($_POST['mtime']) && $_POST['mtime'] != 0) {
		$sql .= ', match_time = '.dbMakeSafe($_POST['mtime']);
	}else {
		$sql .= ', match_time = NULL';
	}
	if(isset($_POST['comment'])) {
		$sql .= ', comment = '.dbPostSafe($_POST['comment']);
	}else {
		$sql .= ', comment = NULL';
	}
	$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND hid = '.dbMakeSafe($hid).';';
	dbQuery($sql);
	dbQuery('COMMIT ;');
	echo '{"cid":'.$cid.',"rid":'.$rid.',"hid":"'.$hid.'"}';

} else {
?><p>Match does not exist</p>
<?php
	dbQuery('ROLLBACK;');
}
dbFree($result);
?>
