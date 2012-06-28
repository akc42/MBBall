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
if(!(isset($_GET['cid']) && isset($_GET['rid']))) forbidden();

$cid=$_GET['cid'];
$rid=$_GET['rid'];


$db->exec("BEGIN TRANSACTION");
$r = $db->prepare("DELETE FROM round WHERE cid = ? AND rid = ? ");
$r->bindInt(1,$cid);
$r->bindInt(2,$rid);
$r->exec();
unset($r);

$r = $db->prepare("UPDATE round SET rid = rid - 1 WHERE cid = ? AND rid > ? ");
$r->bindInt(1,$cid);
$r->bindInt(2,$rid);
$r->exec();
unset($r);

$c = $db->prepare("UPDATE competition SET results_cache = NULL  WHERE cid = ?");
$c->bindInt(1,$cid);
$c->exec();
unset($c);

$db->exec("COMMIT");

echo '{"cid":'.$cid.', "rid":'.$rid.'}';
?>
