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
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['rid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
$cid = $_GET['cid'];
$rid = $_GET['rid'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
if( $cid !=0) {

	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');
	$result = dbQuery('SELECT count(*) from team_in_competition WHERE cid = '.dbMakeSafe($cid).';');
	$row = dbFetchRow($result);
	if($row['count'] > 0) {
		$ticexists = true;
	} else {
		$ticexists = false;
	}
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
	$sql = 'SELECT tid,hid,made_playoff AS mp FROM team_in_competition t LEFT JOIN'; 
 	$sql .= ' (SELECT cid, rid, hid FROM match UNION SELECT cid, rid, aid AS hid FROM match)';
	$sql .= ' AS m ON t.cid = m.cid AND rid= '.dbMakeSafe($rid).' AND t.tid=m.hid WHERE t.cid = '.dbMakeSafe($cid);
	$sql .= ' ORDER BY tid;';
	$result = dbQuery($sql);
	while($row = dbFetchRow($result)) {
		$row['tid'] = trim($row['tid']);
?>	<div id="<?php echo 'T'.$row['tid'];?>" <?php if(!is_null($row['hid']))echo 'class="inmatch"';?>>
		<input type="checkbox" name="<?php echo $row['tid'];?>" <?php
if($row['mp'] == 't') echo 'checked="checked"';?> />
		<span class="tid"><?php echo $row['tid'];?></span>
	</div>
<?php
	}
	dbFree($result);
?>			</td>
			<td id="tnic">
<?php
	$sql = 'SELECT t.tid FROM team t EXCEPT SELECT c.tid FROM team_in_competition c WHERE c.cid = ';
	$sql .= dbMakeSafe($cid).' ORDER BY tid;';
	$result = dbQuery($sql);
	while($row = dbFetchRow($result)) {
		$row['tid'] = trim($row['tid']);
		
?>	<div id="<?php echo 'S'.$row['tid'];?>"><span class="tid"><?php echo $row['tid'];?></span></div>
<?php
	}
	dbFree($result);
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
