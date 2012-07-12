<?php
/*
 	Copyright (c) 2008-2012 Alan Chandler
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
require_once('./inc/db.inc');
if(!(isset($_GET['cid']) && isset($_GET['bbar']) )) forbidden();

$cid = $_GET['cid'];

if ($cid != 0) {
  $s = $db->prepare("SELECT value FROM settings WHERE name = ?");
  $isGuestHeading = $s->fetchSetting('headingisguest');
  $guestApprovedHeading = $s->fetchSetting('headingguestapprove');
  unset($s);
	
	$r = $db->prepare("SELECT * FROM registration r JOIN participant u USING (uid) WHERE r.cid = ? ORDER BY agree_time");
	$r->bindInt(1,$cid);

?>
<table>
	<caption>Registered Users</caption>
	<thead>
		<tr>
			<th class="user_name">Name</th>
			<th>E-Mail</th>
			<th>Last Logon</th>
			<th>When Registered</th>
			<th><?php echo $isGuestHeading; ?></th>
			<th><?php echo $guestApprovedHeading; ?></th>
			<th>Been Admin</th>
			<th>DEL</th>
		</tr>
	</thead>
	<tbody>
<?php
	while($row = $r->FetchRow()) {
?>		<tr>
			<td id="<?php echo 'A'.$row['uid'];?>" class="user_name"><?php echo $row['name'];?></td>
			<td><?php echo $row['email'];?></td>
			<td><span class="time"><?php echo $row['last_logon']; ?></span></td>
			<td><span class="time"><?php echo $row['agree_time']; ?></span></td>
			<td class="radio"><?php if($row['is_guest'] == 1) tick();?></td>
			<td>
				<input type="checkbox" name="<?php echo $row['uid'];?>"
<?php 
		if($row['is_guest'] == 1) {
?>					class="gapprove"
<?php
			 if($_GET['bbar'] == 'false') {
?>					readonly="readonly"
<?php
			}
		} else {
?>					readonly="readonly"
<?php
		}		 
		if($row['approved'] == 1) {
?>					checked="checked"
<?php
		}
?> />
			</td>
			<td class="radio"><?php if($row['admin_experience'] == 1) tick();?></td>
			<td><div id="<?php echo 'F'.$uid; ?>" class="del"></div></td>
		</tr>
<?php
	}
	unset($r);
?>	</tbody>
</table>
<?php
} else {
?><p>No Registered User data is available right now</p>
<?php
}
?>
