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
if(!(isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['hid']) && isset($_GET['aid']) )) forbidden();
$cid=$_GET['cid'];
$rid=$_GET['rid'];
$hid=$_GET['hid'];
$aid=$_GET['aid'];

$db->exec("BEGIN TRANSACTION");

$m = $db->prepare("SELECT * FROM match WHERE cid = ? AND rid = ? AND aid = ?");
$m->bindInt(1,$cid);
$m->bindInt(2,$rid);
$m->bindString(3,$aid);

$row = $m->fetchRow();
unset($m);

if ($row && is_null($row['hid'])) {
	$m = $db->prepare("UPDATE match SET hid = ? WHERE cid = ? AND rid = ? AND aid = ?");
	$m->bindString(1,$hid);
	$m->bindInt(2,$cid);
	$m->bindInt(3,$rid);
	$m->bindString(4,$aid);
	$m->exec();
	unset($m);
	$db->exec("COMMIT");
    echo '{"cid":'.$cid.',"rid":'.$rid.',"hid":"'.$hid.'","aid":"'.$aid.'"}';
} else {
    echo '{"cid":'.$cid.',"rid":'.$rid.',"hid":'.(isset($row)?$row['hid']:'---').',"aid":"'.$aid.'"}'; //respond with something
	$db->exec("ROLLBACK");
}
;
?>
