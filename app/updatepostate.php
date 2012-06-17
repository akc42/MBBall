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
if(!(isset($_GET['cid'])  && isset($_GET['tid']) && isset($_GET['mp']))) forbidden();

$cid=$_GET['cid'];
$tid=$_GET['tid'];
$mp=$_GET['mp'];

$t = $db->prepare("UPDATE team_in_competition SET made_playoff = ? WHERE cid = ? AND tid = ?");
$t->bindInt(1,($mp == 'true')?1:0);
$t->bindInt(2,$cid);
$t->bindString(3,$tid);


echo '{"cid":'.$cid.',"tid":"'.$tid.'","state":'.$mp.'}';
?>
