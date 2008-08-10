<?php
if (!defined('BALL'))
	die('Hacking attempt...');


?>
<table>
	<caption>Players Playoff Picks</caption>
	<thead>
		<tr><th rowspan="3"></th>
<?php
foreach($confs as $confid => $conference) {
?>			<th colspan="<?php echo array_sum($sizes[$confid]);?>"><?php echo $confid; ?></td>
<?php
}
?>			<th rowspan="3">Score</th>
		</tr>
		<tr>
<?php
foreach($confs as $confid => $conference) {
	foreach($divs as $divid => $division){
?>			<th colsspan="<?php echo $sizes[$confid][$divid];?>">
				<?php echo $divid; ?></th>
<?php
	}
}
?>		</tr>
		<tr>
<?php
foreach($confs as $confid => $conference) {
	foreach($divs as $divid => $division) {
		if(isset($teams[$confid][$divid])) {
			foreach($teams[$confid][$divid] as $team) {
?>			<th><?php echo $team['tid'];?></th>
<?php
			}
		}
	}
}
?>		</tr>
	</thead>
	<tbody>
<?php
$sql = 'SELECT u.name AS name, u.uid AS uid, p.score AS score';
$sql .= ' FROM playoff_score p JOIN participant u USING (uid)';
$sql .= ' WHERE cid = '.dbMakeSafe($cid);
$sql .= ' ORDER BY score DESC;';
$result = dbQuery($sql);
while($row = dbFetchRow($result)) {
	$playoff_selections = array();
	$resultplay = dbQuery('SELECT tid  FROM playoff_picks WHERE cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($row['uid']).';');
	while($playdata = dbFetchRow($resultplay)) {
		$playoff_selections[$playdata['tid']] = 1;
	}
?>		<tr>
			<td><?php echo $row['name']; ?></td>
<?php
	foreach($confs as $confid => $conference) {
		foreach($divs as $divid => $division){
			foreach($teams[$confid][$divid] as $team) {
?>				<td <?php if($team['mp'] && isset($playoff_selections[$team['tid']])) echo 'class="win"';?>>
					<?php if(isset($playoff_selections[$team['tid']])) echo $team['tid'];?></td>
<?php
			}
		}
	}
	dbFree($resultplay);
	unset($playoff_selections);
?>			<td><?php echo $row['score'];?></td>
		</tr>
<?php
}
dbFree($result);
?>	</tbody>
</table>
