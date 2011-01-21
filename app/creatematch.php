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
?>

