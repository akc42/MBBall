<?php
/*
 	Copyright (c) 2008,2009,2010 Alan Chandler
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

?><form id="register" action="register.php" <?php if(!$extn_auth) echo 'onsubmit="MBBmgr.checkReg()"'; ?>>
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
<!-- registration block to be floated right -->
	<h1>Register for this Competition</h2>
<?php
if($extn_auth) {
	$cook = unserialize(base64_decode($_COOKIE['MBBall']));		
?>	<input type="hidden" name="uid" value="<?php echo $uid;?>" />
    <input type="hidden" name="name" value="<?php echo $cook['u']['name'];?>" />
    <input type="hidden" name="email" value="<?php echo $cook['u']['email'];?>" />
    <input type="hidden" name="bb" value="<?php echo ($cook['u']['guest'])?'true':'false';?>" />
<?php
} else {
?>	<input type="hidden" name="bb" value="true" />
	<table>
		<tr><th>Username</th><th>e-mail (optional)</th></tr>
		<tr><td><input type="text" name="uid" value=""/></td><input type="email" name="email" value=""/></td></tr>
		<tr><th>Password</th><th>Confirm</th></tr>
		<tr><td><input type="password" name="password" value=""/></td><td><input type="password" name="confirm" value=""/></td></tr>	
	</table>
<?php
}
?>	<div id="reg_submit">
<?php 
if($condition == '') {
?>		<input type="submit" value="Register" />
<?php
} else {
?>		<p><?php if ($extn_auth) echo $name,', ' ;?>In order to enter the competition you must agree to the following condition:-</p>
		<p class="condition"><?php echo dbBBcode($condition);?></p>
		<div id="agree"><input type="submit" value="I Agree" /></div>
<?php
}
if ($approval_required && (!$extn_auth || $cook['u']['guest'])) {
?>		<p class="bbcond"><sup>*</sup>Guests require special approval from the competition administrator before being finally accepted.  Please
		be patient whilst this happens</p>
		
<?php
}
?>
		<div id="regerror"></div>
	</div>
</form>
