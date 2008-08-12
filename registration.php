<?php
if (!defined('BALL'))
	die('Hacking attempt...');
?><form id="register" action="register.php">
	<input type="hidden" name="uid" value="<?php echo $uid;?>" />
	<input type="hidden" name="pass" value="<?php echo $password;?>" />
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
    <input type="hidden" name="name" value="<?php echo $name;?>" />
    <input type="hidden" name="email" value="<?php echo $email;?>" />
    <input type="hidden" name="bb" value="<?php echo (in_array(SMF_BABY,$groups))?'true':'false';?>" />
   
<!-- registration block to be floated right -->
	<h1>Register for this Competition</h2>
<?php 
if($condition == '') {
?>		<input type="submit" value="Register" />
<?php
} else {
?>	<p><?php echo $name ;?>, In order to enter the competition you must agree to the following condition:-</p>
	<p class="condition"><?php echo dbBBcode($condition);?></p>
		<div id="agree"><input type="submit" value="I Agree" /></div>
<?php
}
if (in_array(SMF_BABY,$groups) && $approval_required) {
?>	<p class="bbcond"><sup>*</sup>Baby Backups require special approval from the competition administrator (<?php echo $admName ;?>).
	Please contact her/him <i>after registering</i> if you are a Baby Backup</p>
<?php
}
?></form>
<div id="regerror"></div>
