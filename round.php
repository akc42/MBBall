<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['rid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];
$rid = $_GET['rid'];

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$result = dbQuery('SELECT * FROM round WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ;');
$row = dbFetch($result);
dbFree($result);
$optionresult = dbQuery('SELECT count(*) FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ;');
$optdata= dbFetch($optionresult);

?><form id="roundform" action="updateround.php" >
	<input type="hidden" name="uid" value="<?php echo $uid;?>" />
	<input type="hidden" name="pass" value="<?php echo $pass;?>" />
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
	<input type="hidden" name="rid" value="<?php echo $rid;?>" />
	<div id="questiond">
		<label>Question<br/>
			<textarea id="question" name="question"><?php echo $row['question'];?></textarea>
		</label>
	<div>
	<div id="rnamed"><label>Name<br/>
		<input id="rname" type="text" name="rname" value="<?php echo $row['name'];?>"/></label>
	</div>
	<div id="oud">
		<label><input id="ou" name="ou" type="checkbox" <?php if($row['ou_round'] == 't') echo 'checked="checked"';?> />Use Over Under Selection</label>
	</div>
	<div id="valued">
		<label>Points for correct pick</br>
			<input id="value" name="value" type="text" value="<?php echo $row['value'];?>" />
		</label>
	</div>
	<div id="validquestiond">
		<label><input id=validquestion" name="validquestion" type="checkbox" 
			<?php if ($row['valid_question'] == 't') echo 'checked="checked"';?> />Valid Question?</label>
	</div>
	<div id="deadlined">
		<label>Deadline for answering question<br/>
			<input id="deadline" type="text" class="time" value="<?php echo $row['deadline'];;?>"/>
		</label>
	</div>
	<div id="submitd">
		<input type="submit" value="Update" />
	</div>
	<div id="answerd">
		<label>Answer<br/>
			<input id="answer" name="answer" value="<?php echo $row['answer'];?>" 
				<?php if($optdata['count') > 0) echo 'disabled="disabled"';?> />
		</label>
	</div>
</form>
<div id="option"></div> <!-- marker to start option drag to create new option -->
<div id="match"></div> <!-- market to start match drag to create new match -->

