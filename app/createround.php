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
if(!(isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['rname']) )) forbidden();

$cid=$_POST['cid'];
$rid=$_POST['rid'];

$db->exec("BEGIN TRANSACTION");
$c = $db->prepare("SELECT COUNT(*) FROM competition WHERE cid = ?");
$c->bindInt(1,$cid);
$noComps = $c->fetchValue();
unset($c);
if ($noComps != 0) {
	$r = $db->prepare("INSERT INTO round(cid,rid,name,ou_round) VALUES (?,?,?,?)");
	$r->bindInt(1,$cid);
	$r->bindInt(2,$rid);
	$r->bindString(3,$_POST['rname']);
	if(isset($_POST['ou'])) {
		$r->bindInt(4,1);
	} else {
		$r->bindInt(4,0);
	}
	$r->exec();
	unset($r);
	$db->exec("COMMIT");
	echo '{"cid":'.$cid.',"rid":'.$rid.'}';
} else {
  echo '<p>Related Competition Does Not Exist</p>';
	$db->exec("ROLLBACK");
}
?>
