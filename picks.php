<?php
// Copyright (c) 2008 Alan Chandler - licenced under the GPL (see COPYING.txt in this directory)
if (!defined('BALL'))
	die('Hacking attempt...');
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
?>
		</thead>
		<tbody>
<?php
	
	dbFree($result);
/*
	// we need to get the users resultant score
$sql = 'SELECT u.name AS name, u.uid AS uid,r.score AS rscore, r.score AS score';
$sql .= ' FROM round_score r JOIN participant u USING (uid)';
$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid);
$sql .= ' ORDER BY score DESC;';
$result = dbQuery($sql);
$sql = 'SELECT * FROM round_score r JOIN match m USING (cid,rid) JOIN team t ON t.tid = m.hid';
$sql .= ' LEFT JOIN pick p USING (cid,rid,hid,uid)'; 
$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND m.open IS TRUE ORDER BY r.score DESC, t.confid, t.divid, m.hid;'; 
$resultmatch = dbQuery($sql);
	if ($matchdata = dbFetch($resultmatch)) {
		$i = 0;
		while ($row = dbFetchRow($result)) {
			for($match = $i*$nomatches;$match++;$match < ($i+1)*$nomatches) {
?>			<tr>
				<td><?php echo $row['name'];?></td>
<?php
				if($rounddata['ou_round']) {
?>				<td <?php if(!is_null($matchdata[$match]['pid']) && !is_null($matchdata[$match]['hscore']) &&
						 !is_null($matchdata[$match]['ascore']) && 
						($matchdata[$match]['pid'] == $matchdata[$match]['hid'] &&
						$matchdata[$match]['hscore']>$matchdata[$match]['ascore']) ||
						($matchdata[$match]['pid'] == $matchdata[$match]['aid'] && 
						$matchdata[$match]['hscore']<$matchdata[$match]['ascore']))
							echo 'class="win"' ;?>>
					<?php if(!is_null($matchdata[$match]['pid'])) echo $matchdata[$match]['pid'];?>
				</td>
				<td <?php if(!is_null($matchdata[$match]['over']) && !is_null($matchdata[$match]['hscore']) &&
						 !is_null($matchdata[$match]['ascore']) &&
						($matchdata[$match]['over'] == 't' && 
						($matchdata[$match]['combined_score']+0.5 < $matchdata[$match]['hscore']+$matchdata[$match]['ascore'])) || 
						(!$matchdata[$match]['over'] == 't' && 
						($matchdata[$match]['combined_score']+0.5 > $matchdata[$match]['hscore']+$matchdata[$match]['ascore'])))
							echo 'class="win"';?>>
					<?php if(!is_null($matchdata[$match]['over'])) echo ($matchdata[$match]['over'] == 't')?'Over':'Under';?>
				</td>
<?php
				} else {
?>				<td colspan="2" <?php if(!is_null($matchdata[$match]['pid']) && !is_null($matchdata[$match]['hscore']) &&
						!is_null($matchdata[$match]['ascore']) && 
						($matchdata[$match]['pid'] == $matchdata[$match]['hid'] &&
						 $matchdata[$match]['hscore']>$matchdata[$match]['ascore']) ||
						($matchdata[$match]['pid'] == $matchdata[$match]['aid'] && 
						$matchdata[$match]['hscore']<$matchdata[$match]['ascore']))
							echo 'class="win"' ;?>>
					<?php if(!is_null($matchdata[$match]['pid'])) echo $matchdata[$match]['pid'];?>
				</td>
<?php
				}
				if($rounddata['valid_question'] == 't') {
					if($bqopts > 0) {
				// this is a multichoice question, so get results and output them
					dbRestartQuery($resultbq);  //reset lost of options to start
						while ($optdata = dbFetchRow($resultbq)) {
?>				<td <?php if($optdata['oid'] == $rounddata['answer'] && $row['rscore'] > 0) 
					echo 'class="win"';?>><?php if($row['rscore'] > 0) echo 'X';?></td>
<?php
						}
					} else {
?>				<td <?php if($row['rscore'] > 0) echo 'class="win"';?>>
					<?php if($row['rscore'] > 0) echo $rounddata['answer'];?></td>
<?php
					}
				}
?>				<td><?php echo $row['score']; ?></td>	
			</tr>
<?php
			}
			$i++;
		}
	}
	dbFree($result);
*/
?>	</tbody>
</table>
<table>
<?php
		if($nom < 8) {
			$moreMatchesToCome = false;
		} else {
			$startMatch += 8;
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


//  Need per user data against these headings

?>	</tbody>
</table>
<?php
}




