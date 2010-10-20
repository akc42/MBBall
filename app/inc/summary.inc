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

?><table>
	<caption>Overall Results</caption>
	<thead>
		<tr><th class="user_name">Name</th>
<?php
$result = $db->prepare("SELECT name FROM round WHERE open = 1  AND rid <= ? ORDER BY rid DESC LIMIT ? ;");
$result->bindValue(1,$rid,PDO::PARAM_INT);
$result->bindValue(2,$max_rounds_display,PDO::PARAM_INT);
$result->execute();
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
?>			<th><?php echo $row['name']; ?></th>
<?php
}
$result->closeCursor();
?>			<th class="score">Rounds<br/>Total</th>
			<th class="score">PlayOffs<br/>Total</th>
			<th class="score">Grand<br/>Total</th>
		</tr>
	</thead>
	<tbody>
<?php
if ($rid != 0) {
	$result = $db->prepare("
		SELECT u.uid, u.name AS name, p.pscore, sum(r.score) AS rscore, p.pscore+sum(r.score) AS total
		FROM participant u JOIN registration reg USING (uid) JOIN
		(SELECT uid, sum(score) AS pscore FROM playoff_score GROUP BY uid) AS p USING (uid) JOIN round_score r USING (uid)
		WHERE rid <= ? GROUP BY u.uid, u.name,p.pscore  ORDER BY total DESC,u.name;
	");
	$result->bindValue(1,$rid,PDO::PARAM_INT);
} else {
	$result->prepare("
		SELECT u.uid, u.name AS name, 0 AS pscore, 0 AS rscore, 0 AS total FROM participant u WHERE u.agree_time IS NOT NULL ORDER BY u.name;
	");
}
$result->execute();
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	
?>		<tr>
			<td <?php if($uid == $row['uid']) {echo 'class="user_name me"';} else {echo 'class="user_name"';}?>><?php echo $row['name'];?></td>
<?php
	$resultround = $db->prepare("SELECT score FROM round_score WHERE rid <= ? AND uid = ? ORDER BY rid DESC LIMIT ? ;");
	$resultround->bindValue(1,$rid,PDO::PARAM_INT);
	$resultround->bindValue(2,$row['uid'],PDO::PARAM_INT);
	$resultround->bindValue(3,$max_rounds_display,PDO::PARAM_INT);
	$resultround->execute();
	while ($rscore = $resultround->fetch(PDO::FETCH_ASSOC)) {
?>			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $rscore['score'];?></td>
<?php
	}
	$resultround->closeCursor();
	unset($resultround);
?>			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $row['rscore'];?></td>
			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $row['pscore'];?></td>
			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $row['total'];?></td>
		</tr>
<?php
}
$result->closeCursor();
?>	</tbody>
</table>
