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
$sql = 'SELECT name,question,count(

<div id="rounddetail">
	<form id="roundform" action="updateround.php" >
		<input type="hidden" name="uid" value="<?php echo $uid;?>" />
		<input type="hidden" name="pass" value="<?php echo $pass;?>" />
		<input type="hidden" name="cid" value="<?php echo $cid;?>" />
		<input type="hidden" name="rid" value="<?php echo $rid;?>" />
		<div id="rnamed"><label>Name<br/>
			<input id="rname" type="text" name="rname" value="<?php echo $row['name'];?>"/></label>
		</div>
		<div id="questiond"><label>Question<br/>
			<textarea id="question" name="question"><?php echo $row['question'];?></textarea></label>
			<input id=validquestion" name="validquestion" type="checkbox" 
				<?php if ($row['valid_question'] == 't') echo 'checked="checked"';?> />
		</div>
	
		<input type="submit" value="Update" />
	</form>
<div>
<hr />
<div id="matchdatadata"></div>
