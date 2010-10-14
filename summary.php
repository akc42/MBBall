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
if (!defined('BALL'))
	die('Hacking attempt...');
?><table>
	<caption>Overall Results</caption>
	<thead>
		<tr><th class="user_name">Name</th>
<?php
$result = dbQuery('SELECT name FROM round WHERE open IS TRUE AND cid = '.dbMakeSafe($cid).' AND rid <= '.dbMakeSafe($rid).' ORDER BY rid DESC LIMIT '.MBBALL_MAX_ROUND_DISPLAY.' ;');
while($row = dbFetchRow($result)) {
?>			<th><?php echo $row['name']; ?></th>
<?php
}
dbFree($result);
?>			<th class="score">Rounds<br/>Total</th>
			<th class="score">PlayOffs<br/>Total</th>
			<th class="score">Grand<br/>Total</th>
		</tr>
	</thead>
	<tbody>
<?php
if ($rid != 0) {
	$sql = 'SELECT u.uid, u.name AS name, p.pscore, sum(r.score) AS rscore, p.pscore+sum(r.score) AS total';
	$sql .= ' FROM participant u JOIN registration reg USING (uid) JOIN';
	$sql .= ' (SELECT cid,uid, sum(score) AS pscore FROM playoff_score GROUP BY cid,uid) AS p';
	$sql .= ' USING (cid,uid) JOIN round_score r USING (cid,uid)';
	$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid <= '.dbMakeSafe($rid);
	$sql .= ' GROUP BY u.uid, u.name,p.pscore  ORDER BY total DESC,u.name;';
} else {
	$sql .= 'SELECT u.uid, u.name AS name, 0 AS pscore, 0 AS rscore, 0 AS total';
	$sql .= ' FROM participant u JOIN registration reg USING (uid) WHERE cid = '.dbMakeSafe($cid).' ORDER BY u.name;';
}
$result = dbQuery($sql);
while($row = dbFetchRow($result)) {
	
?>		<tr>
			<td <?php if($uid == $row['uid']) {echo 'class="user_name me"';} else {echo 'class="user_name"';}?>><?php echo $row['name'];?></td>
<?php
	$resultround = dbQuery('SELECT score FROM round_score WHERE cid = '.dbMakeSafe($cid).' AND
				 rid <= '.dbMakeSafe($rid).' AND uid = '.dbMakeSafe($row['uid']).' ORDER BY rid DESC LIMIT '.MBBALL_MAX_ROUND_DISPLAY.' ;');
	while ($rscore = dbFetchRow($resultround)) {
?>			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $rscore['score'];?></td>
<?php
	}
?>			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $row['rscore'];?></td>
			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $row['pscore'];?></td>
			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $row['total'];?></td>
		</tr>
<?php
}
?>	</tbody>
</table>
