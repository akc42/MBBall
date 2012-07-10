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
if(!(isset($_GET['cid'])  && isset($_GET['tid']))) forbidden();

$cid=$_GET['cid'];
$tid=$_GET['tid'];

$SQL = "SELECT COUNT(*) FROM (SELECT t.tid FROM team_in_competition t JOIN playoff_picks USING (cid,tid)";
$SQL .= " WHERE t.cid = ? AND t.tid = ? UNION ALL SELECT t.tid FROM team_in_competition t";
$SQL .= " JOIN match m ON (t.tid = m.aid OR t.tid = m.hid) WHERE t.cid = ? AND t.tid = ?)";
$c = $db->prepare($SQL);  //Counts how many records reference the one we want to delete
$c->bindInt(1,$cid);
$c->bindString(2,$tid);
$c->bindInt(3,$cid);
$c->bindString(4,$cid);
$references = $c->fetchValue();
unset($c);
if($references == 0) {

  $t = $db->prepare("DELETE FROM team_in_competition WHERE cid = ? AND tid = ?");
  $t->bindInt(1,$cid);
  $t->bindString(2,$tid);
  $t->exec();
  echo '{"cid":'.$cid.',"tid":"'.$tid.'","OK":true }';
  unset($t);
} else {
  echo '{"cid":'.$cid.',"tid":"'.$tid.'","OK":false }';
}
?>
