<?php
// Copyright (c) 2008 Alan Chandler - licenced under the GPL (see COPYING.txt in this directory)
if (!defined('BALL'))
	die('Hacking attempt...');

// we need to get the users resultant score
$sql = 'SELECT u.name , u.uid , r.mscore,r.bscore,r.score, p.opid, p.comment';
$sql .= ' FROM round_score r JOIN participant u USING (uid)';
$sql .= ' LEFT JOIN option_pick p USING (cid,rid,uid)';
$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid);
$sql .= ' ORDER BY score DESC;';
$resultuser = dbQuery($sql);


$isAQuestion = ($rounddata['valid_question'] == 't');
$ouRound = ($rounddata['ou_round'] == 't');
$totalHasBeenOutput = false;
$moreMatchesToCome = true;
$startMatch = 0;
?><h1>Details of this rounds pick</h1>
<?php
while ($moreMatchesToCome) {
	$result = dbQuery('SELECT * FROM match m JOIN team t ON m.hid = t.tid WHERE m.cid = '
			.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid)
			.' AND  m.open IS TRUE ORDER BY t.confid, t.divid, hid LIMIT 8 OFFSET '.$startMatch.';');
?><table>
	<thead>
		<tr><th rowspan="<?php echo ($ouRound)?4:3 ;?>" class="match_data">Match Data</th>
<?php

	if(($nom = dbNumRows($result)) > 0) {
		while($row = dbFetchRow($result)) {
			if(!(is_null($row['hscore']) || is_null($row['ascore']) || $row['hscore'] < $row['ascore'])) {
			//Home win
?>			<th class="win">
<?php
			} else {
?>			<th>
<?php
			}
			echo $row['hid'];
?>			</th>
<?php
			if(!(is_null($row['hscore']) || is_null($row['ascore']) || $row['hscore'] > $row['ascore'])) {
			//Away win
?>			<th class="win">
<?php
			} else {
?>			<th>
<?php
			}
			echo $row['aid'];
?>			</th>
<?php
		}

?>			<th rowspan="2">Points for<br/>Correct Pick</th>
<?php

		// if we have less than eight matches left and there is noly the overall total to output we should do it rather than create a new table
		if($nom < 8 && !$isAQuestion) {
			$totalHasBeenOutput = true;
?>			<th rowspan="<?php echo ($ouRound)?4:3 ;?>">Round Score</th>
<?php
		}
?>		</tr>
		<tr>
<?php
				//first three rows first column covered by rowspan
		dbRestartQuery($result);  //put the results back to the start so we can interate over them again
		while($row = dbFetchRow($result)) {
?>			<th colspan="2"><span class="time"><?php echo $row['match_time'];?></span></th>
<?php
		}
?>		</tr>
		<tr>
<?php
		dbRestartQuery($result);  //put the results back to the start so we can interate over them again
		while($row = dbFetchRow($result)) {
?>			<th><?php if(!is_null($row['hscore'])) echo $row['hscore'];?></th>
			<th><?php if(!is_null($row['ascore'])) echo $row['ascore'];?></th>
<?php
		}
?>			<th><?php echo $rounddata['value'];?></th>
		</tr>
<?php
		if($ouRound) {
?>		<tr>
<?php
			dbRestartQuery($result);  //put the results back to the start so we can interate over them again
			while($row = dbFetchRow($result)) {
			//This is an over or under guessing round, so we need to also show the over/under results
			$cs = $row['combined_score']+0.5;
				if(!(is_null($row['hscore']) || is_null($row['ascore']))) {
					$scores=$row['hscore']+$row['ascore'];
?>			<th><?php echo $cs;?></th>
			<th><?php echo ($scores>$cs)?'Over':'Under';?></th>
<?php
				} else {
?>			<th><?php echo $cs;?></th><th></th>
<?php
				}
			}
?>			<th></th>
		</tr>
<?php
		}
	
?>	</thead>
	<tbody>
<?php
		while ($userdata = dbFetchRow($resultuser)) {
?>			<tr>
				<td rowspan="2"><?php echo $userdata['name'];?></td>
<?php
			dbRestartQuery($result);
			while ($row=dbFetchRow($result)) {
				$pick = dbQuery('SELECT * FROM pick WHERE cid = '.dbMakeSafe($cid)
							.' AND rid = '.dbMakeSafe($rid).' AND hid = \''.$row['hid'].'\' AND uid = '.$userdata['uid'].';');
				if($pickdata = dbFetchRow($pick)) {
					if(!is_null($row['hscore']) && !is_null($row['ascore'])) {
						if ((($row['hscore']>$row['ascore'])?$row['hid']:$row['aid']) == $pickdata['pid']) {
?>				<td class="win">
<?php
							echo $pickdata['pid'].'<span class="win"><br/>(Right)</span>';
						} else {
?>				<td>
<?php
							echo $pickdata['pid'];
						}
?>				</td>
<?php
						if($ouRound) {
							if ($row['hscore']+$row['ascore'] > $row['combined_score']+0.5) {
								if($pickdata['over'] == 't') {
?>				<td class="win">Over<span class="win"><br/>(Right)</span></td>
<?php
								} else {
?>				<td>Under</td>
<?php
								}
							} else {
								if ($pickdata['over'] == 't') {
?>				<td>Over</td>
<?php
								} else {
?>				<td class="win">Under<span class="win"><br/>(Right)</span></td>
<?php
								}
							}
						} else {
?>				<td></td>
<?php
						}
					} else {
?>				<td colspan="2"></td>
<?php
					}
				}
			}
			if($totalHasBeenOutput) {
?>				<td rowspan="2"><?php echo $userdata['score'];?></td>
<?php
			}
?>			</tr>
			<tr>
<?php
			dbRestartQuery($result);
			while ($row=dbFetchRow($result)) {
				$pick = dbQuery('SELECT * FROM pick WHERE cid = '.dbMakeSafe($cid)
					.' AND rid = '.dbMakeSafe($rid).' AND hid = \''.$row['hid'].'\' AND uid = '.$userdata['uid'].';');
				if($pickdata = dbFetchRow($pick)) {
?>				<td colspan="2"><?php echo dbBBcode($pickdata['comment']);?></td>
<?php
				} else {
?>				<td colspan="2"></td>
<?php
				}
			}
?>			</tr>
<?php
		}
		dbFree($result);

?>	</tbody>
</table>
<?php
		dbRestartQuery($resultuser);
		if($nom < 8) {
				$moreMatchesToCome = false;
		} else {
			$startMatch += 8;
			dbRestartQuery($resultuser);
		}
	} else {
		$moreMatchesToCome = false;
	}
}
// Now we have done the matches, we may need to do a special table for the bonus question

if(!$totalHasBeenOutput) {
?><table>
	<thead>
		<tr><th rowspan="<?php echo ($isAQuestion)?3:1 ;?>" class="match_data">Round Data</th>
<?php		
	if($isAQuestion) {
	//We need to find all the options so we can display them later, but for now we need to know how many for the colspan
		$resultbq=dbQuery('SELECT * FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ORDER BY opid;');
		$bqopts = dbNumRows($resultbq);
?>			<th <?php if($bqopts > 0) echo 'colspan="'.$bqopts.'"';?>>Bonus Question</th>
			<th rowspan="3">Bonus Score</th>
			<th rowspan="3">Pick Score</th>
<?php
	}
?>			<th rowspan="<?php echo ($isAQuestion)?3:1 ;?>">Total<br/>Round Score</th>
		</tr>
<?php
	if($isAQuestion) {
?>		<tr>
		<th <?php if($bqopts > 0) echo 'colspan="'.$bqopts.'"';?>><?php echo dbBBcode($rounddata['question']);?></th>
		</tr>
		<tr>
<?php
		if($bqopts > 0) {
			// this is a multichoice question, so get results and output them
			while ($optdata = dbFetchRow($resultbq)) {
?>			<th <?php if(!is_null($rounddata['answer']) && $rounddata['answer'] == $optdata['opid']) echo 'class="win"';?>>
					<?php echo $optdata['label'];?></th>
<?php
			}
		} else {
?>			<th><?php if(!is_null($rounddata['answer'])) echo $rounddata['answer'];?></th>
<?php
		}
?>		</tr>
<?php
	}
?>	</thead>
	<tbody>
<?php
	while ($userdata = dbFetchRow($resultuser)) {

?>		<tr>
			<td><?php echo $userdata['name'];?></td>
<?php
		if($isAQuestion) {
			if($bqopts > 0) {
				// this is a multichoice question, so get results and output them
				dbRestartQuery($resultbq);  //reset lost of options to start
				while ($optdata = dbFetchRow($resultbq)) {
					if($optdata['opid'] == $userdata['opid']) {
						if($userdata['opid'] == $rounddata['answer']) {
?>			<td class="win">X<span class="win"><br/>(Right)</span></td>
<?php
						} else {
?>			<td>X</td>
<?php
						}
					
					} else {
						if($userdata['opid'] == $rounddata['answer']) {
?>			<td class="win"><?php echo $userdata['opid'];?><span class="win"><br/>(Right)</span></td>
<?php
						} else {
?>			<td><?php echo $userdata['opid'];?></td>
<?php
						}
					}
				}
			} else{
				if($userdata['opid'] == $rounddata['answer']) {
?>			<td class="win"><?php echo $userdata['opid'];?><span class="win"><br/>(Right)</span></td>
<?php
				} else {
?>			<td><?php echo $userdata['opid'];?></td>
<?php
				}
			}	
?>			<td><?php echo $userdata['bscore'];?></td><td><?php echo $userdata['mscore'];?></td>
<?php
		}	
?>			<td><?php echo $userdata['score'];?></td>
		</tr>
<?php
	}
?>	</tbody>
</table>
<?php
}
