<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['rid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];
$rid = $_GET['rid'];
if($rid != 0 && $cid !=0) {

	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');


?><div id="matchtemplate" class="matchd hidden">
	<form action="match.php" >
		<input type="hidden" value="<?php echo $cid;?>"/>
		<input type="hidden" value="<?php echo $rid;?>"/>
		<label class="open"><input type="checkbox" name="open" /><span>Open</span></label>
		<div class="hid"></div><div class="aid"></div>
		<input type="text" class="mtime" />
		<input type="text" class="cscore" /><input type="text" class="hscore" /><input type="text" class="ascore" />
		<textarea class="comment"></textarea>
		<input type="submit" class="submit" value="Save" />
	</form> 
</div>

<?php
	$result = dbQuery('SELECT * FROM match WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ;');
	while($row = dbFetchRow($result)) {
?><div class="matchd">
	<form action="match.php" >
		<input type="hidden" value="<?php echo $cid;?>"/>
		<input type="hidden" value="<?php echo $rid;?>"/>
		<label class="open"><input type="checkbox" name="open" <?php if($row['open'] == 't') echo 'checked="checked"';?>/>
			<span>Open</span></label>
		<div class="hid"><?php echo $row['hid'];?></div>
		<div class="aid"><?php echo $row['aid'];?></div>
		<input type="text" class="mtime time" value="<?php echo $row['match_time'];?>" />
		<input type="text" class="cscore" 
			value="<?php echo $row['combined_score'];?>" 
			<?php if(!isset($_GET['ou'])) echo 'disabled="disabled"';?> />
		<input type="text" class="hscore" value="<?php echo $row['hscore'];?>"/>
		<input type="text" class="ascore" value="<?php echo $row['ascore'];?>"/>
		<textarea class="comment"><?php echo $row['comment'];?></textarea>
		<input type="submit" class="submit" value="Save" />
	</form> 
<?php

	}
	dbFree($result);
} else {
?><p>No Match information available right now</p>
<?php
}
