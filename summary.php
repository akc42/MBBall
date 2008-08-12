<?php
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
?>			<th>Rounds<br/>Total</th>
<?php
	if($playoff_deadline != 0) {
?>			<th>PlayOffs<br/>Total</th>
<?php
}
?>			<th>Grand<br/>Total</th>
		</tr>
	</thead>
	<tbody>
<?php
if ($rid != 0) {
	$sql = 'SELECT u.name AS name, p.score AS pscore, sum(r.score) AS rscore, p.score+sum(r.score) AS total';
	$sql .= ' FROM participant u JOIN registration reg USING (uid) JOIN playoff_score p USING (cid,uid) JOIN round_score r USING (cid,uid)';
	$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid <= '.dbMakeSafe($rid);
	$sql .= ' GROUP BY u.name, p.score ORDER BY total DESC;';
} else {
	$sql .= 'SELECT u.name AS name, 0 AS pscore, 0 AS rscore, 0 AS total';
	$sql .= ' FROM participant u JOIN registration reg USING (uid) WHERE cid = '.dbMakeSafe($cid).' ORDER BY u.name;';
}
$result = dbQuery($sql);
while($row = dbFetchRow($result)) {
?>		<tr>
			<td class="user_name"><?php echo $row['name'];?></td>
<?php
	$resultround = dbQuery('SELECT score FROM round_score WHERE cid = '.dbMakeSafe($cid).' AND
				 rid <= '.dbMakeSafe($rid).' AND uid = '.dbMakeSafe($uid).' ORDER BY rid DESC;');
	while ($rscore = dbFetchRow($resultround)) {
?>			<td><?php echo $rscore['score'];?></td>				
<?php
	}
?>			<td><?php echo $row['rscore'];?></td><td><?php echo $row['pscore'];?></td><td><?php echo $row['total'];?></td>
		</tr>
<?php
}
?>	</tbody>
</table>
