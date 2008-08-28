<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['hid']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_GET['cid'];
$rid=$_GET['rid'];
$hid=$_GET['hid'];

dbQuery('BEGIN ;');
$result=dbQuery('SELECT * FROM match WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND hid = '.dbMakeSafe($hid).';');
if (dbNumRows($result) == 0) {
  dbQuery('INSERT INTO match(cid, rid, hid) VALUES ('.dbMakeSafe($cid).','.dbMakeSafe($rid).','.dbMakeSafe($hid).');');
  dbQuery('COMMIT ;');
?><form action="#" >
     <input type="hidden" name="uid" value="<?php echo $uid;?>" />
     <input type="hidden" name="pass" value="<?php echo $password;?>" />
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
  dbQuery('ROLLBACK ;');
}
dbFree($result);

