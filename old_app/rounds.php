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
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];
if ($cid != 0) {
	require_once('./db.inc');
	$result = dbQuery('SELECT rid,name,ou_round,open FROM round WHERE cid = '.dbMakeSafe($cid).' ORDER BY rid DESC ;');
	
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
	while($row = dbFetchRow($result)) {
		$rid = $row['rid'];
?>		<tr>
			<td id="<?php echo 'R'.$rid;?>" class="selectthis"><?php echo $rid; ?></td>
			<td id="<?php echo 'S'.$rid;?>" class="selectthis"><?php echo $row['name'];?></td>
			<td><div id="<?php echo 'E'.$rid; ?>" class="del">
					<input type="hidden" name="open" value="<?php echo ($row['open'] == 't')?$rid:0;?>" /></div></td>
		</tr>
<?php
	}
	dbFree($result);

?>	</tbody>
</table>
<?php
} else {
?><p>No Round Data</p>
<?php
}
?>
