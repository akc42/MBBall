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
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['rid']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];
$rid = $_GET['rid'];
if($rid != 0 && $cid !=0) {
	if(!isset($_GET['answer']))
		die('Hacking attempt - wrong parameters');
	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');
	
	$optionresult = dbQuery('SELECT * FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ORDER BY opid;');
	$noopts = dbNumRows($optionresult);
?><form id="optionform">
<table>
     <caption>Multichoice Answers</caption>
	<thead>
		<tr>
			<th class="radio">Correct</th>
			<th class="option_choice">Choice</th>
			<th class="del_head">DEL</th>
		</tr>
	</thead>

     <tbody>
<?php 
     if ($noopts != 0) {
?>       <tr>
         <td><input id="nullanswer" type="radio" name="option" value="0" <?php if ($_GET['answer'] == 0) echo 'checked="checked"';?> /></td>
         <td colspans="2"><span>No Answer Set Yet</span></td>
         </tr>
<?php
     }
     while($row = dbFetchRow($optionresult)) {
	$opid = $row['opid'];
	$noopts = max($noopts,$opid);
?>	<tr>
	  <td><input type="radio" value="<?php echo $opid ;?>" name="option" <?php if($opid == $_GET['answer']) echo 'checked="checked"';?> class="option_select"/></td>
	  <td><input type="text" name="<?php echo $opid; ?>" value="<?php echo $row['label'];?>" class="option_input"/></td>
	  <td><div id="<?php echo 'O'.$opid; ?>" class="del"></div></td>
	</tr>
<?php
     }
     dbFree($optionresult);
?>	</tbody>
</table>
<input id="noopts" type="hidden" name="noopts" value="<?php echo $noopts;?>" />
</form>
<?php
} else {
?><p>No Option information available right now</p>
<?php
}
?>


