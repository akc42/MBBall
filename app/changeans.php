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
if(!(isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['opid']) && isset($_GET['label']) )) forbidden();

$cid=$_GET['cid'];
$rid=$_GET['rid'];
$opid=$_GET['opid'];

$db->exec("BEGIN TRANSACTION");

$o = $db->prepare("SELECT COUNT(*) FROM option WHERE cid = ? AND rid = ? AND opid = ? ");
$o->bindInt(1,$cid);
$o->bindInt(2,$rid);
$o->bindInt(3,$opid);

if ($o->fetchValue() != 0) {
	unset($o);
	$o = $db->prepare("UPDATE OPTION SET label = ? WHERE cid = ? AND rid = ? AND opid = ? ");
	$o->bindString(1,$_GET['label']);
	$o->bindInt(1,$cid);
	$o->bindInt(2,$rid);
	$o->bindInt(3,$opid);
	$o->exec();
	unset($o);
	$db->exec("COMMIT");
	echo '{"cid":'.$cid.',"rid":'.$rid.',"opid":'.$opid.',"label":"'.$_GET['label'].'"}';
} else {
	unset($o);
?><p>Option does not exist</p>
<?php
	$db->exec("ROLLBACK");
}
?>

