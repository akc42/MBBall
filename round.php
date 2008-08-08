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
	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');
	$result = dbQuery('SELECT * FROM round WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ;');
	$row = dbFetchRow($result);
	dbFree($result);
	$optionresult = dbQuery('SELECT count(*) FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ;');
	$optdata= dbFetch($optionresult);
	dbFree($optionresult);
?>
<form id="roundform" action="updateround.php" >
	<input type="hidden" name="uid" value="<?php echo $uid;?>" />
	<input type="hidden" name="pass" value="<?php echo $password;?>" />
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
	<input type="hidden" name="rid" value="<?php echo $rid;?>" />
	<table class="form">
		<caption>Round Details</caption>
		<tbody>
			<tr>
				<td>
		<label>Round Name<br/>
		<input id="rname" type="text" name="rname" class="rname" value="<?php echo $row['name'];?>"/></label>
				</td>
				<td rowspan="3" colspan="2">
		<label>Question<br/>
			<textarea id="question" name="question"><?php echo $row['question'];?></textarea>
		</label>
				</td>
			</tr>
			<tr>
				<td class="option1">
		<label><input id="ou" name="ou" type="checkbox" <?php if($row['ou_round'] == 't') echo 'checked="checked"';?> />Use Over Under Selection</label>
				</td>
			</tr>
			<tr>
				<td>
		<label>Points for correct pick<br/>
			<input id="value" name="value" type="text" value="<?php echo $row['value'];?>" />
		</label>
				</td>
			</tr>
			<tr>
				<td>
		<label><input id="roundopen" name="open" type="checkbox" 
			<?php if ($row['open'] == 't') echo 'checked="checked"';?> />Round Open</label>
				</td>
				<td>
		<label><input id="validquestion" name="validquestion" type="checkbox" 
			<?php if ($row['valid_question'] == 't') echo 'checked="checked"';?> />Valid Question?</label>
				</td>
				<td id="option">
		<label>Answer<br/>
			<input id="answer" name="answer" value="<?php echo $row['answer'];?>" 
				<?php if($optdata['count'] > 0) echo 'disabled="disabled"';?> />
		</label>
				</td>
			</tr>
			<tr>
				<td colspan="2">
		<label>Deadline for answering question<br/>
			<input id="deadline" name="deadline" type="text" class="time" value="<?php echo $row['deadline'];;?>"/>
		</label>
				</td>
				<td class="submit">
		<input type="submit" value="Update" />
				</td>
			</tr>
		</tbody>
	</table>
</form>
<?php
} else {
?><p>There is no Round information to display right now</p>
<?php
}
