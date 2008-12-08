<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if (!defined('BALL'))
	die('Hacking attempt...');
?><table>
	<caption>Overall Results</caption>
	<thead>
		<tr><th class="user_name">Name</th>
<?php
$result = dbQuery('SELECT name FROM round WHERE open IS TRUE AND cid = '.dbMakeSafe($cid).' AND rid <= '.dbMakeSafe($rid).' ORDER BY rid DESC;');
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
				 rid <= '.dbMakeSafe($rid).' AND uid = '.dbMakeSafe($row['uid']).' ORDER BY rid DESC;');
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
