<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
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

	$result = dbQuery('SELECT * FROM match WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ORDER BY match_time NULLS FIRST, hid ;');
	while($row = dbFetchRow($result)) {

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
		<label><input type="checkbox" name="open" <?php if($row['open'] == 't') echo 'checked';?>/>Open</label>
					</div>
					<div class="del"></div>
					<div class="hscore">
		<input type="text" name="hscore" value="<?php echo $row['hscore'];?>"/>
					</div>
					<div class="ascore">
		<input type="text" name="ascore" value="<?php echo $row['ascore'];?>"/>
					</div>
					<div class="cscore">
		<input type="text" name="cscore" value="<?php echo $row['combined_score'];?>" <?php if($_GET['ou'] != 'true') echo 'readOnly';?> />
					</div>
					<div class="mtime">
		<input type="text" class="time" name="mtime" value="<?php echo $row['match_time'];?>" />
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
