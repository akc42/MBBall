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
if(!(isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['hid']) )) forbidden();

$cid=$_POST['cid'];
$rid=$_POST['rid'];
$hid=$_POST['hid'];

$db->exec("BEGIN TRANSACTION");
$m = $db->prepare("SELECT COUNT(*) FROM match WHERE cid = ? AND rid = ? AND hid = ?");
$m->bindInt(1,$cid);
$m->bindInt(2,$rid);
$m->bindString(3,$hid);
$noMatch = $m->fetchValue();
unset($m);
if ($noMatch != 0) {
	
	$sql = "UPDATE match SET open = ?, hscore = ?, ascore = ?,combined_score = ?, match_time = ?, comment = ? ";
	$sql .= " WHERE cid = ? AND rid = ? AND hid = ?";
	$sql = 'UPDATE match SET';
	$m = $db->prepare($sql);
	$m->bindInt(1,isset($_POST['open'])?1:0);
	if(isset($_POST['hscore'])) {
		$m->bindInt(2,$_POST['hscore']);
	}else {
		$m->bindNull(2);
	}
	if(isset($_POST['ascore'])) {
		$m->bindInt(3,$_POST['ascore']);
	}else {
		$m->bindNull(3);
	}
	if(isset($_POST['cscore'])) {
		$m->bindInt(4,$_POST['cscore']);
	}else {
		$m->bindNull(4);
	}
	if(isset($_POST['mtime']) && $_POST['mtime'] != 0) {
		$m->bindInt(5,$_POST['mtime']);
	}else {
		$m->bindNull(5);
	}
	if(isset($_POST['comment'])) {
		$m->bindString(6,$_POST['comment']);
	}else {
		$m->bindNull(6);
	}
	$m->bindInt(7,$cid);
	$m->bindInt(8,$rid);
	$m->bindString(9,$hid);
	$m->exec();
	unset($m);
	$db->exec("COMMIT");
	echo '{"cid":'.$cid.',"rid":'.$rid.',"hid":"'.$hid.'"}';

} else {
?><p>Match does not exist</p>
<?php
	$db->exec("ROLLBACK");
}
?>
