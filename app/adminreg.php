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
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['bbar']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];


if ($cid != 0) {
	require_once('./db.inc');
	$result = dbQuery('SELECT * FROM registration r JOIN participant u USING (uid) WHERE r.cid = '.dbMakeSafe($cid).' ORDER BY agree_time ;');

?>
<table>
	<caption>Registered Users</caption>
	<thead>
		<tr>
			<th class="user_name">Name</th>
			<th>E-Mail</th>
			<th>Last Logon</th>
			<th>When Registered</th>
			<th>Is a BB</th>
			<th>BB Approved</th>
			<th>Been Admin</th>
			<th>DEL</th>
		</tr>
	</thead>
	<tbody>
<?php
	while($row = dbFetchRow($result)) {
?>		<tr>
			<td id="<?php echo 'A'.$row['uid'];?>" class="user_name"><?php echo $row['name'];?></td>
			<td><?php echo $row['email'];?></td>
			<td><span class="time"><?php echo $row['last_logon']; ?></span></td>
			<td><span class="time"><?php echo $row['agree_time']; ?></span></td>
			<td class="radio"><?php if($row['is_bb'] == 't') tick();?></td>
			<td>
				<input type="checkbox" name="<?php echo $row['uid'];?>"
<?php 
		if($row['is_bb'] == 't') {
?>					class="bbapprove"
<?php
			 if($_GET['bbar'] == 'false') {
?>					readonly="readonly"
<?php
			}
		} else {
?>					readonly="readonly"
<?php
		}		 
		if($row['bb_approved'] == 't') {
?>					checked="checked"
<?php
		}
?>								 />
			</td>
			<td class="radio"><?php if($row['admin_experience'] == 't') tick();?></td>
			<td><div id="<?php echo 'F'.$uid; ?>" class="del"></div></td>
		</tr>
<?php
	}
	dbFree($result);

?>	</tbody>
</table>
<?php
} else {
?><p>No Registered User data is available right now</p>
<?php
}
?>
