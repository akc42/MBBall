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
?><form id="register" action="register.php">
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
    <input type="hidden" name="name" value="<?php echo $name;?>" />
    <input type="hidden" name="email" value="<?php echo $email;?>" />
    <input type="hidden" name="bb" value="<?php echo ($guest)?'true':'false';?>" />
   
<!-- registration block to be floated right -->
	<h1>Register for this Competition</h2>
<?php 
if($condition == '') {
?>		<input type="submit" value="Register" />
<?php
} else {
	require_once('./bbcode.inc');
?>	<p><?php echo "$name ,".MBBALL_CONDITION;?></p>
	<p class="condition"><?php echo dbBBcode($condition);?></p>
		<div id="agree"><input type="submit" value="I Agree" /></div>
<?php
}
if ($guest && $approval_required) {
	$guestNote = str_replace("$$$",$admName,MBBALL_GUESTNOTE);
?>	<p class="bbcond"><sup>*</sup><?php echo $guestNote ;?></p>
<?php
}
?></form>
<div id="regerror"></div>
