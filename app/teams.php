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
if(!(isset($_GET['cid']) && isset($_GET['rid']))) forbidden();

$cid = $_GET['cid'];
$rid = $_GET['rid'];

if( $cid !=0) {
	$t = $db->prepare("SELECT COUNT(*) FROM team_in_competition WHERE cid = ?");
	$t->bindInt(1,$cid);
	if($t->fetchValue() > 0) {
		$ticexists = true;
	} else {
		$ticexists = false;
	}
	unset($t);
?><table>
	<caption>Teams</caption>
	<thead>
		<tr>
			<th class="team">TiC</th>
			<th class="team">Teams</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td id="tic">
<?php
	$sql = "SELECT tid,hid,made_playoff AS mp FROM team_in_competition t LEFT JOIN ("; 
 	$sql .= " SELECT cid, rid, hid FROM match UNION SELECT cid, rid, aid AS hid FROM match ";
	$sql .= " ) m ON t.cid = m.cid AND rid= ? AND t.tid=m.hid WHERE t.cid = ? ORDER BY tid ";
	$t = $db->prepare($sql);
	$t->bindInt(1,$rid);
	$t->bindInt(2,$cid);
	while($row = $t->FetchRow()) {
		$row['tid'] = trim($row['tid']);
?>	<div id="<?php echo 'T'.$row['tid'];?>" <?php if(!is_null($row['hid']))echo 'class="inmatch"';?>>
		<input type="checkbox" name="<?php echo $row['tid'];?>" <?php
if($row['mp'] == 1) echo 'checked="checked"';?> />
		<span class="tid"><?php echo $row['tid'];?></span>
	</div>
<?php
	}
	unset($t);
?>			</td>
			<td id="tnic">
<?php
	$sql = "SELECT t.tid AS tid FROM team t EXCEPT SELECT c.tid AS tid FROM team_in_competition c WHERE c.cid = ? ORDER BY tid";
	$t = $db->prepare($sql);
	$t->bindInt(1,$cid);
	while($row = $t->FetchRow()) {
		$row['tid'] = trim($row['tid']);
		
?>	<div id="<?php echo 'S'.$row['tid'];?>"><span class="tid"><?php echo $row['tid'];?></span></div>
<?php
	}
	unset($t);
?>			</td>
		</tr>
		<tr>
			<td>
				<label id="lock_cell"><input id="lock" type="checkbox" <?php if($ticexists) echo 'checked="checked"';?> />Lock</label>
			</td>
			<td><div id="addall"></div></td>
		</tr>
	</tbody>
</table>
<?php
} else {
?><p>No Team information available right now</p>
<?php
}
?>
