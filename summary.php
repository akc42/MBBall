<?php
if (!defined('BALL'))
	die('Hacking attempt...');
?><table>
	<caption>Overall Results</caption>
	<thead>
		<tr><th>Name</th>
<?php
$result = dbQuery('SELECT name FROM round WHERE open IS TRUE AND cid = '.dbMakeSafe($cid).' AND rid <= '.dbMakeSafe($rid).' ORDER BY rid DESC;');
while($row = dbFetchRow($result)) {
?>			<th><?php echo $row['name']; ?></th>
<?php
}
dbFree($result);
	if($playoff_deadline != 0) {
?>			<th>PlayOffs</th>
<?php
}
?>			<th>Total</th>
		</tr>
	</thead>
	<tbody>
<?php
$sql = 'SELECT u.name AS name, p.score AS pscore, sum(r.score) AS rscores, p.score+sum(r.score) AS total';
$sql .= ' FROM participant u JOIN registration reg USING (uid) JOIN playoff_score p USING (cid,uid) JOIN round_score r USING (cid,uid)';
$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid <= '.dbMakeSafe($rid);
$sql .= ' GROUP BY u.name, p.score ORDER BY total DESC;';
$result = dbQuery($sql);
while($row = dbFetchRow($result)) {
?>		<tr>
			<td><?php echo $row['name'];?></td>
<?php
	$resultround = dbQuery('SELECT score FROM round_score WHERE cid = '.dbMakeSafe($cid).' AND
				 rid <= '.dbMakeSafe($rid).' AND uid = '.dbMakeSafe($uid).' ORDER BY rid DESC;');
	while ($rscore = dbFetchRow($resultround)) {
?>			<td><?php echo $rscore['score'];?></td>				
<?php
	}
?>		</tr>
<?php
}
?>	</tbody>
</table>
