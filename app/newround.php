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
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['mr']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];
if($cid !=0) {

	$rid = $_GET['mr'];
	
	require_once('./db.inc');
?>
<form id="createroundform" action="createround.php">
	<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
	<input type="hidden" name="pass" value="<?php echo $password; ?>" />
	<input type="hidden" name="cid" value="<?php echo $cid; ?>" />
	<input id="nextrid" type="hidden" name="rid" value="<?php echo $rid; ?>" />
	<table class="form">
		<caption>Create Round</caption>
		<tbody>
			<tr>
				<td><?php echo $rid; ?></td>
				<td><input id="rname2" class="rname" type="text" name="rname" value="Round <?php echo $rid;?>" /></td>
				<td class="option2"><label><input id="ou2" type="checkbox" name="ou" />Over/Under</label></td>
				<td class="submit"><input type="submit" value="Create" /></td>
			</tr>
		</tbody>
	</table>
</form>
<?php
} else {
?><p>Cannot create New Round right now</p>
<?php
}
?>

