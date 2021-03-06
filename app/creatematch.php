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
if(!(isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['aid']) )) forbidden();


$cid=$_GET['cid'];
$rid=$_GET['rid'];
$aid=$_GET['aid'];

if($cid == 0 || $rid == 0) forbidden(); //Both cid and rid must be non zero

$db->exec("BEGIN TRANSACTION");
$m = $db->prepare("SELECT COUNT(*) FROM match WHERE cid = ? AND rid = ? AND  aid = ?");
$m->bindInt(1,$cid);
$m->bindInt(2,$rid);
$m->bindInt(3,$aid);

$noMatches = $m->fetchValue();
unset($m);


if ($noMatches == 0) {
	$m=$db->prepare("INSERT INTO match(cid,rid,aid) VALUES (?,?,?)");
	$m->bindInt(1,$cid);
	$m->bindInt(2,$rid);
	$m->bindString(3,$aid);
	$m->exec();
	unset($m);
	$db->exec("COMMIT");
?><form action="updatematch.php" >
     <input type="hidden" name="cid" value="<?php echo $cid;?>"/>
     <input type="hidden" name="rid" value="<?php echo $rid;?>"/>
     <input type="hidden" name="aid" value="<?php echo $aid;?>" />
     <input type="hidden" name="hid" />
     <input type="hidden" name="underdog" value="0" />   <div>
     <div class="aid"><span><?php echo $aid ;?></span></div><div class="at">@</div>
     <div class="hid"><span>---</span></div>
     <div class="open"><label><input type="checkbox" name="open" />Open</label></div>
     <div class="del"></div>
    </div>
    <div class="clear">
      <div class="ascore"><input type="text" name="ascore"/></div>
      <div class="hscore"><input type="text" name="hscore"/></div>
      <div class="cscore"><input type="text" name="cscore" /></div>
    </div>
  <div class="mtime clear"><input type="hidden" name="mtime"  /></div>
  <div class="comment clear"><textarea name="comment"></textarea></div>
  <div class="underdog clear">
    <div class="slider"><div class="knob">0</div></div>
  </div>
</form> 
<div class="clear"></div>
<?php
} else {
?><p>Match already exists</p>
<?php
  $db->exec("ROLLBACK");
}
?>

