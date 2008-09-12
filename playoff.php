<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if (!defined('BALL'))
	die('Hacking attempt...');
require_once('team.php');


?><h1>Players Playoff Picks</h1>
<?php
foreach($confs as $confid => $conference) {
	$sql = 'SELECT u.name, u.uid, p.score FROM registration r JOIN participant u USING (uid)';
	$sql .= ' LEFT JOIN playoff_score p ON r.cid = p.cid AND r.uid = p.uid AND p.confid = '.dbMakeSafe($confid);
	$sql .= ' WHERE r.cid = '.dbMakeSafe($cid);
	$sql .= ' ORDER BY score DESC, u.name;';
	$result = dbQuery($sql);

?><table>
	<thead>
		<tr><th rowspan="2"><?php echo $conference;?></th>
<?php
	foreach($divs as $divid => $division){
?>			<th colspan="<?php echo $sizes[$confid][$divid];?>">
				<?php echo $division; ?></th>
<?php
	}

?>			<th rowspan="2" class="score">Score</td>
		</tr>
		<tr>
<?php

	foreach($divs as $divid => $division) {
		if(isset($teams[$confid][$divid])) {
			foreach($teams[$confid][$divid] as $team) {
?>			<th class="tid"><?php if($team['mp']) {echo tick();}echo $team['tid'];?></th>
<?php
			}
		}
	}
?>		</tr>
	</thead>
	<tbody>
<?php
	while($row = dbFetchRow($result)) {
		$playoff_selections = array();
		$resultplay = dbQuery('SELECT tid  FROM playoff_picks JOIN team t USING (tid) '
				.' WHERE cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($row['uid']).' AND t.confid = '.dbMakeSafe($confid).' ;');
		while($playdata = dbFetchRow($resultplay)) {
			$playoff_selections[trim($playdata['tid'])] = 1;
		}
?>		<tr>
			<td class="user_name"><?php echo $row['name']; ?></td>
<?php
		foreach($divs as $divid => $division){
			foreach($teams[$confid][$divid] as $team) {
				$correct = ($team['mp'] && isset($playoff_selections[$team['tid']]));
?>				<td <?php if($correct) {echo 'class="tid win"';}else{ echo 'class="tid"';}?>>
					<?php if(isset($playoff_selections[$team['tid']])) echo '<img src="images/sel.gif" alt="'.$team['tid'].' selected"/>';if($correct) tick();?></td>
<?php
			}
		}
	
		dbFree($resultplay);
		unset($playoff_selections);
?>			<td class="score"><?php echo $row['score'];?></td>
		</tr>
<?php
	}
	dbFree($result);
?>	</tbody>
</table>
<?php
}
?>
