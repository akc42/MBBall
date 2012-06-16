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
if(!isset($_GET['cid'])) forbidden();

$cid = $_GET['cid'];

if ($cid != 0) {
	$r = $db->prepare("SELECT rid,name,ou_round,open FROM round WHERE cid = ? ORDER BY rid DESC");
	$r->bindInt(1,$cid);
?>
<table>
	<caption>Rounds</caption>
	<thead>
		<tr>
			<th class="radio">No</th>
			<th>Round Name</th>
			<th class="del_head">DEL</th>
		</tr>
	</thead>
	<tbody>
<?php
	while($row = $r->FetchRow()) {
		$rid = $row['rid'];
?>		<tr>
			<td id="<?php echo 'R'.$rid;?>" class="selectthis"><?php echo $rid; ?></td>
			<td id="<?php echo 'S'.$rid;?>" class="selectthis"><?php echo $row['name'];?></td>
			<td><div id="<?php echo 'E'.$rid; ?>" class="del">
					<input type="hidden" name="open" value="<?php echo ($row['open'] == 1)?$rid:0;?>" /></div></td>
		</tr>
<?php
	}
	unset($r);

?>	</tbody>
</table>
<?php
} else {
?><p>No Round Data</p>
<?php
}
?>
