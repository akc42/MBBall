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
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['uid'];
$password = $_POST['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
require_once('./db.inc');
$cid=$_POST['cid'];
$name=$_POST['name'];
$email=$_POST['email'];
$bb=$_POST['bb'];
dbQuery('BEGIN ;');
$result = dbQuery('SELECT * FROM participant WHERE uid = '.dbMakeSafe($uid).';');
if(dbNumRows($result) == 0) {
    dbQuery('INSERT INTO participant (uid,name,email,last_logon, is_bb) VALUES ('
.dbMakeSafe($uid).','.dbPostSafe($name).','.dbPostSafe($email).', DEFAULT,'.$bb.');');

}
dbFree($result);
$result = dbQuery('SELECT u.uid AS uuid, r.uid AS ruid FROM participant u LEFT JOIN registration r ON u.uid = r.uid AND cid = '
	.dbMakeSafe($cid).' WHERE u.uid = '.dbMakeSafe($uid).';');
$row = dbFetchRow($result);
if ($row && is_null($row['ruid'])) {
	dbQuery('INSERT INTO registration(cid,uid,agree_time) VALUES ('.dbMakeSafe($cid).','.dbMakeSafe($uid).',DEFAULT);');
	dbQuery('COMMIT ;');
	echo '{"cid":'.$cid.',"uid":"'.$uid.'"}';
} else {
	dbQuery('ROLLBACK ;');
	echo 'Error Registering. Please tell webmaster@melindasbackups.com';
}
dbFree($result);
?>
