<?php
/*
 	Copyright (c) 2012 Alan Chandler
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
if(!(isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['aid']) )) forbidden();

$cid=$_GET['cid'];
$rid=$_GET['rid'];
$aid=$_GET['aid'];

$c = $db->prepare("SELECT COUNT(*) FROM match m JOIN pick p USING (cid,rid,aid) WHERE m.cid = ? AND rid = ? AND aid=?");
$c->bindInt(1,$cid);
$c->bindInt(2,$rid);
$c->bindString(3,$aid);

echo '{"cid":'.$cid.',"rid":'.$rid.',"aid":"'.$aid.'","referers":'.$c->fetchValue().'}';
unset($c);
?>