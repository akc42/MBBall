<?php
/*
 	Copyright (c) 2008-2012 Alan Chandler
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
require_once('./inc/db.inc');
if(!(isset($_POST['cid']))) forbidden();

$cid=$_POST['cid'];
$name=$_POST['name'];
$email=$_POST['email'];
$bb=$_POST['bb'];

$db->exec("BEGIN TRANSACTION");
$u = $db->prepare("SELECT COUNT(*) FROM participant WHERE uid = ?");
$u->bindInt(1,$uid);
$noUser = $u->fetchValue();
unset($u);
if($noUser == 0) {
	$u = $db->prepare("INSERT INTO participant(uid,name,email,last_logon,is_guest) VALUES (?,?,?,(strftime('%s','now')),?)");
	$u->bindInt(1,$uid);
	$u->bindString(2,$name);
	$u->bindString(3,$email);
	$u->bindInt(4,($bb == 'true')?1:0);	
	$u->exec();
	unset($u);	
}
$u = $db->prepare("SELECT u.uid AS uuid, r.uid AS ruid FROM participant u LEFT JOIN registration r ON u.uid = r.uid WHERE r.cid = ? AND u.uid = ?");
$u->bindInt(1,$cid);
$u->bindInt(2,$uid);
$row = $u->FetchRow();
unset($u);
if ($row && is_null($row['ruid'])) {
	$r = $db->prepare("INSERT INTO registration(cid,uid) VALUES (?,?");
	$r->bindInt(1,$cid);
	$r->bindInt(2,$rid);
	$r->exec();
	unset($r);
	$db->exec("COMMIT");
	echo '{"cid":'.$cid.',"uid":"'.$uid.'"}';
} else {
	$db->exec("ROLLBACK");
	echo 'Error Registering. Please tell webmaster@melindasbackups.com';
}
?>
