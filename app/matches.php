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

$cid = $_GET['cid'];
$rid = $_GET['rid'];

if($rid != 0 && $cid !=0) {
	if (!isset($_GET['ou'])) forbidden();
	
	$m = $db->prepare("SELECT * FROM match WHERE cid = ? AND rid = ? ORDER BY match_time,aid");
	$m->bindInt(1,$cid);
	$m->bindInt(2,$rid);
	while($row = $m->FetchRow()) {
		$row['hid']=trim($row['hid']);
		$row['aid']=trim($row['aid']);

?><div class="match">
  <form  action="updatematch.php" >
    <input type="hidden" name="cid" value="<?php echo $cid;?>"/>
    <input type="hidden" name="rid" value="<?php echo $rid;?>"/>
    <input type="hidden" name="aid" value="<?php echo $row['aid'];?>" />
    <input type="hidden" name="hid" value="<?php echo $row['hid'];?>" />
    <input type="hidden" name="underdog" value="<?php echo $row['underdog'];?>" />
    <div>
      <div class="aid"><span><?php echo $row['aid'];?></span></div><div class="at">@</div>
      <div class="hid"><span><?php echo ((is_null($row['hid']) || $row['hid'] == "")? '---':$row['hid']);?></span></div>
      <div class="open">
	<label><input type="checkbox" name="open" <?php if($row['open'] == 1) echo 'checked="checked"';?>/>Open</label>
      </div>
      <div class="del"></div>
   </div>
   <div class="clear">
      <div class="ascore">
	<input type="text" name="ascore" value="<?php echo $row['ascore'];?>"/>
      </div>
      <div class="hscore">
	<input type="text" name="hscore" value="<?php echo $row['hscore'];?>"/>
      </div>
      <div class="cscore">
	<input type="text" name="cscore" value="<?php echo $row['combined_score'];?>" <?php if($_GET['ou'] != 'true') echo 'readonly="readonly"';?> />
      </div>
    </div>
    <div class="mtime clear">
      <input type="hidden" name="mtime" value="<?php echo $row['match_time'];?>" />
    </div>
    <div class="comment">
      <textarea name="comment clear"><?php echo $row['comment'];?></textarea>
    </div>
    <div class="underdog clear">
      <div class="slider"><div class="knob"><?php echo abs($row['underdog']);?></div></div>
    </div>
  </form> 
  <div class="clear"></div>
</div>
<?php

	}
	unset($m);
} else {
?><p>No Match information available right now</p>
<?php
}
?>
