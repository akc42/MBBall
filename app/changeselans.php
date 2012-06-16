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
if(!(isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['opid']) )) forbidden();

$cid=$_GET['cid'];
$rid=$_GET['rid'];
$opid=$_GET['opid'];

$db->exec("BEGIN TRANSACTION");
$a = $db->prepare("SELECT answer FROM round WHERE cid = ? AND rid = ? ");
$a->bindInt(1,$cid);
$a->bindInt(2,$rid);
$answer = $a->fetchValue();
unset($a);
if ($answer) {
	if($opid != 0) {
		
		$o = $db->prepare("SELECT COUNT(*) FROM option WHERE cid = ? AND rid = ? AND opid = ?");
		$o->bindInt(1,$cid);
		$o->bindInt(2,$rid);
		$o->bindInt(3,$opid);
		$op = $o->fetchValue();
		unset($o);
		if ($op != 0) {
			if ($answer != $opid) {
					$r = $db->prepare("UPDATE round SET answer = ? WHERE cid = ? AND rid = ? ");
					$r->bindInt(1,$opid);
					$r->bindInt(2,$cid);
					$r->bindInt(3,$rid);
					$r->exec();
					unset($r);
			}
			$db->exec("COMMIT");
			echo '{"cid":'.$cid.',"rid":'.$rid.',"opid":'.$opid.'}';
		} else {
?><p>Option matching answer does not exist</p>
<?php
			$db->exec("ROLLBACK");
		}
	} else {
		$r = $db->prepare("UPDATE round SET answer = 0 WHERE cid = ? AND rid = ? ");
		$r->bindInt(1,$cid);
		$r->bindInt(2,$rid);
		$r->exec();
		unset($r);
		$db->exec("COMMIT");
		echo '{"cid":'.$cid.',"rid":'.$rid.',"opid":0}';
	}
} else {
?><p>Round does not exist</p>
<?php
  $db->exec("ROLLBACK");
}
?>
