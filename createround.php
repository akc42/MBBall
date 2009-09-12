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
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['rname']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['uid'];
$password = $_POST['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_POST['cid'];
$rid=$_POST['rid'];
dbQuery('BEGIN ;');
$result=dbQuery('SELECT * FROM competition WHERE cid = '.dbMakeSafe($cid).';');
if (dbNumRows($result) != 0) {
	if(isset($_POST['ou'])) {
		dbQuery('INSERT INTO round(cid, rid, name,ou_round) VALUES ('.dbMakeSafe($cid).','.dbMakeSafe($rid).','
			.dbPostSafe($_POST['rname']).', TRUE );');
	} else {
		dbQuery('INSERT INTO round(cid, rid, name, ou_round) VALUES ('.dbMakeSafe($cid).','.dbMakeSafe($rid).','
			.dbPostSafe($_POST['rname']).', FALSE );');
	}
	dbQuery('COMMIT ;');
	echo '{"cid":'.$cid.',"rid":'.$rid.'}';
} else {
  echo '<p>Related Competition Does Not Exist</p>';
	dbQuery('ROLLBACK ;');
}
dbFree($result);
?>
