<?php
/*
 	Copyright (c) 2008,2009 Alan Chandler
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
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['rid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];
$rid = $_GET['rid'];
if($rid != 0 && $cid !=0) {
	if (!isset($_GET['ou']))
		die('Hacking attempt - wrong parameters');
	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');

	$result = dbQuery('SELECT * FROM match WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ORDER BY match_time, hid ;');
	while($row = dbFetchRow($result)) {
		$row['hid']=trim($row['hid']);
		$row['aid']=trim($row['aid']);

?><div class="match">
     <form  action="#" >
     <input type="hidden" name="uid" value="<?php echo $uid;?>" />
     <input type="hidden" name="pass" value="<?php echo $password;?>" />
		<input type="hidden" name="cid" value="<?php echo $cid;?>"/>
		<input type="hidden" name="rid" value="<?php echo $rid;?>"/>
		<input type="hidden" name="hid" value="<?php echo $row['hid'];?>" />
		<input type="hidden" name="aid" value="<?php echo $row['aid'];?>" />
					<div class="hid"><span><?php echo $row['hid'];?></span></div><div class="at">@</div>
					<div class="aid"><span><?php echo (is_null($row['aid'])? '---':$row['aid']);?></span></div>
					<div class="open">
		<label><input type="checkbox" name="open" <?php if($row['open'] == 't') echo 'checked="checked"';?>/>Open</label>
					</div>
					<div class="del"></div>
					<div class="hscore">
		<input type="text" name="hscore" value="<?php echo $row['hscore'];?>"/>
					</div>
					<div class="ascore">
		<input type="text" name="ascore" value="<?php echo $row['ascore'];?>"/>
					</div>
					<div class="cscore">
		<input type="text" name="cscore" value="<?php echo $row['combined_score'];?>" <?php if($_GET['ou'] != 'true') echo 'readonly="readonly"';?> />
					</div>
					<div class="mtime">
		<input type="hidden" name="mtime" value="<?php echo $row['match_time'];?>" />
					</div>
					<div class="comment">
		<textarea name="comment"><?php echo $row['comment'];?></textarea>
					</div>
	</form> 
	<div class="clear"></div>
</div>
<?php

	}
	dbFree($result);
} else {
?><p>No Match information available right now</p>
<?php
}
?>
