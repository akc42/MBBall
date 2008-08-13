<?php
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
         <td><input id="nullanswer" type="radio" name="option" value="0" <?php if ($_GET['answer'] == 0) echo 'checked';?> /></td>
         <td colspans="2"><span>No Answer Set Yet</span></td>
         </tr>
<?php
     }
     while($row = dbFetchRow($optionresult)) {
	$opid = $row['opid'];
	$noopts = max($noopts,$opid);
?>	<tr>
	  <td><input type="radio" value="<?php echo $opid ;?>" name="option" <?php if($opid == $_GET['answer']) echo 'checked';?> class="option_select"/></td>
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


