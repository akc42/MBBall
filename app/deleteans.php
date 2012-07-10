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
$p = $db->prepare("SELECT COUNT(*) FROM option WHERE cid = ? AND rid = ? AND opid = ?");
$p->bindInt(1,$cid);
$p->bindInt(2,$rid);
$p->bindInt(3,$opid);
$noOpts = $p->fetchValue();
unset($p);

if ($noOpts > 0) {
	$p = $db->prepare("DELETE FROM option WHERE cid = ? AND rid = ? AND opid =?");
	$p->bindInt(1,$cid);
	$p->bindInt(2,$rid);
	$p->bindInt(3,$opid);
	$p->exec();
	unset($p);	
	$db->exec("COMMIT");
    echo '{"cid":'.$cid.',"rid":'.$rid.', "opid":"'.$opid.'"}';

} else {
?><p>Option doesn't exist</p>
<?php
	$db->exec("ROLLBACK");
}
?>
