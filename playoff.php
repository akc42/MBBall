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
		} else {
?><th class="tid">&nbsp;</th>
<?php
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
    		if(isset($teams[$confid][$divid])) {
			    foreach($teams[$confid][$divid] as $team) {
				    $correct = ($team['mp'] && isset($playoff_selections[$team['tid']]));
    ?>				<td <?php if($correct) {echo 'class="tid win"';}else{ echo 'class="tid"';}?>>
					    <?php if(isset($playoff_selections[$team['tid']])) echo '<img src="images/sel.gif" alt="'.$team['tid'].' selected"/>';if($correct) tick();?></td>
    <?php
			    }
    		} else {
?><td class="tid">&nbsp;</td>
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
