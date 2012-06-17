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
if(!(isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['hid']) )) forbidden();

$cid=$_GET['cid'];
$rid=$_GET['rid'];
$hid=$_GET['hid'];

$db->exec("BEGIN TRANSACTION");
$m = $db->prepare("SELECT COUNT(*) FROM match WHERE cid = ? AND rid = ? AND  hid = ?");
$m->bindInt(1,$cid);
$m->bindInt(2,$rid);
$m->bindInt(3,$hid);

$noMatches = $m->fetchValue();
unset($m);

if ($noMatches == 0) {
	$m=$db->prepare("INSERT INTO match(cid,rid,hid) VALUES (?,?,?)");
	$m->bindInt(1,$cid);
	$m->bindInt(2,$rid);
	$m->bindString(3,$hid);
	$m->exec();
	unset($m);
	$db->exec("COMMIT");
?><form action="#" >
     <input type="hidden" name="cid" value="<?php echo $cid;?>"/>
     <input type="hidden" name="rid" value="<?php echo $rid;?>"/>
     <input type="hidden" name="hid" value="<?php echo $hid;?>" />
     <input type="hidden" name="aid" />
     <div class="hid"><span><?php echo $hid ;?></span></div><div class="at">@</div>
     <div class="aid"><span>---</span></div>
     <div class="open"><label><input type="checkbox" name="open" />Open</label></div>
     <div class="del"></div>
     <div class="hscore"><input type="text" name="hscore"/></div>
  <div class="ascore"><input type="text" name="ascore"/></div>
  <div class="cscore"><input type="text" name="cscore" /></div>
  <div class="mtime"><input type="hidden" name="mtime"  /></div>
  <div class="comment"><textarea name="comment"></textarea></div>
</form> 
<div class="clear"></div>
<?php
} else {
?><p>Match already exists</p>
<?php
  $db->exec("ROLLBACK");
}
?>

