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
if(!(isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['aid']) )) forbidden();

$cid=$_POST['cid'];
$rid=$_POST['rid'];
$aid=$_POST['aid'];

$db->exec("BEGIN TRANSACTION");
$m = $db->prepare("SELECT COUNT(*) FROM match WHERE cid = ? AND rid = ? AND aid = ?");
$m->bindInt(1,$cid);
$m->bindInt(2,$rid);
$m->bindString(3,$aid);
$noMatch = $m->fetchValue();
unset($m);
if ($noMatch != 0) {
	
  $sql = "UPDATE match SET open = ?, hscore = ?, ascore = ?,combined_score = ?, match_time = ?, comment = ?, underdog = ? ";
  $sql .= " WHERE cid = ? AND rid = ? AND aid = ?";

  $m = $db->prepare($sql);
  $m->bindInt(1,isset($_POST['open'])?1:0);
  if($_POST['hscore'] != '') {
    $m->bindInt(2,$_POST['hscore']);
  }else {
    $m->bindNull(2);
  }
  if($_POST['ascore'] != '') {
    $m->bindInt(3,$_POST['ascore']);
  }else {
    $m->bindNull(3);
  }
  if($_POST['cscore'] != '') {
    $m->bindInt(4,$_POST['cscore']);
  }else {
    $m->bindNull(4);
  }
  if($_POST['mtime'] != 0) {
    $m->bindInt(5,$_POST['mtime']);
  }else {
    $m->bindNull(5);
  }
  if($_POST['comment'] != '') {
    $m->bindString(6,$_POST['comment']);
  }else {
    $m->bindNull(6);
  }
  $m->bindInt(7,$_POST['underdog']);
  $m->bindInt(8,$cid);
  $m->bindInt(9,$rid);
  $m->bindString(10,$aid);
  $m->exec();
  unset($m);
  if(isset($_POST['open'])) {
    //Only need to break cache if this is an open match
    $c = $db->prepare("UPDATE competition SET results_cache = NULL  WHERE cid = ?");
    $c->bindInt(1,$cid);
    $c->exec();
    unset($c);
    
    $r = $db->prepare("UPDATE round SET results_cache = NULL WHERE cid =? AND rid = ?");
    $r->bindInt(1,$cid);
    $r->bindInt(2,$rid);
    $r->exec();
    unset($r);
  }

  $db->exec("COMMIT");
  echo '{"cid":'.$cid.',"rid":'.$rid.',"aid":"'.$aid.'"}';

} else {
?><p>Match does not exist</p>
<?php
  $db->exec("ROLLBACK");
}
?>
