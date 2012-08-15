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

$u = $db->prepare("SELECT u.uid AS uuid, r.uid AS ruid FROM participant u LEFT JOIN registration r ON u.uid = r.uid AND r.cid = ? WHERE u.uid = ?");
$u->bindInt(1,$cid);
$u->bindInt(2,$uid);
$row = $u->FetchRow();
unset($u);
if ($row && is_null($row['ruid'])) {
	$r = $db->prepare("INSERT INTO registration(cid,uid) VALUES (?,?)");
	$r->bindInt(1,$cid);
	$r->bindInt(2,$uid);
	$r->exec();
	unset($r);
	// We need to invalidate cache's to ensure our see the new player
	$c = $db->prepare("UPDATE competition SET results_cache = NULL  WHERE cid = ?");
	$c->bindInt(1,$cid);
	$c->exec();
	unset($c);
	$db->exec("COMMIT");
	echo '{"cid":'.$cid.',"uid":"'.$uid.'"}';
} else {
	$db->exec("ROLLBACK");
	echo '<p>Error Registering. Please tell webmaster@melindasbackups.com</p>';
}
?>
