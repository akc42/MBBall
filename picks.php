<?php
// Copyright (c) 2008 Alan Chandler - licenced under the GPL (see COPYING.txt in this directory)
if (!defined('BALL'))
	die('Hacking attempt...');

// we need to get the users resultant score
$sql = 'SELECT u.name , u.uid , r.pscore, r.oscore, r.mscore,r.bscore,r.score, p.opid, p.comment';
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
/* GROUPS OF 8 MATCHES ----------------------------------------------------------------------------*/
while ($moreMatchesToCome) {
	$result = dbQuery('SELECT * FROM match m JOIN team t ON m.hid = t.tid WHERE m.cid = '
			.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid)
			.' AND  m.open IS TRUE ORDER BY t.confid, t.divid, hid LIMIT 8 OFFSET '.$startMatch.';');
	$nom = dbNumRows($result);

?><table>
	<thead>
		<tr>
			<th rowspan="<?php echo ($nom == 0)?2:(($ouRound)?5:4) ;?>" class="match_data">Match Data<br/><?php echo $rounddata['name'];?></th>
			<th rowspan="<?php echo ($nom == 0)?1:2 ;?>" class="score">Points for<br/>Correct Pick</th>
<?php
/* TEAM NAMES IN HEADER ---------------------------------------------------------------*/
	if($nom > 0) {
		while($row = dbFetchRow($result)) {
			if(!(is_null($row['hscore']) || is_null($row['ascore']) || $row['hscore'] < $row['ascore'])) {
			//Home win
?>			<th class="win tid"><?php
			} else {
?>			<th class="tid"><?php
			}
			echo $row['hid']; ?></th>
<?php
			if(!(is_null($row['hscore']) || is_null($row['ascore']) || $row['hscore'] > $row['ascore'])) {
			//Away win
?>			<th class="win tid"><?php
			} else {
?>			<th class="tid"><?php
			}
			echo $row['aid'];?></th>
<?php
		}

?>			
<?php
	}
		// if we have less than eight matches left and there is noly the overall total to output we should do it rather than create a new table
	if($nom < 8 && !$isAQuestion) {
		$totalHasBeenOutput = true;
?>			<th rowspan="<?php echo ($nom==0)?2:(($ouRound)?5:4) ;?>" class="score">Round Score</th>
<?php
	}
?>		</tr>
<?php
	if ($nom > 0) {	
?>		<tr>
<?php
/*  MATCH TIME ROW IN HEADER --------------------------------------------------------------------------*/
			//first three rows first column covered by rowspan
		dbRestartQuery($result);  //put the results back to the start so we can interate over them again
		while($row = dbFetchRow($result)) {
?>			<th colspan="2"><span class="time"><?php echo $row['match_time'];?></span></th>
<?php
		}
	}
/* THE VALUE OF A CORRECT PICK ----------------------*/
?>		</tr>
		<tr>
			<th class="score" rowspan="<?php echo ($nom==0)?1:(($ouRound)?3:2) ;?>"><?php echo $rounddata['value'];?></th>
<?php
/* THE SCORES IN HEADER ------------------------------------------------------------------------------*/
	if ($nom > 0) {
		dbRestartQuery($result);  //put the results back to the start so we can interate over them again
		while($row = dbFetchRow($result)) {
?>			<th class="score" ><?php if(!is_null($row['hscore'])) echo $row['hscore'];?></th>
			<th class="score"><?php if(!is_null($row['ascore'])) echo $row['ascore'];?></th>
<?php
		}
?>		</tr>
<?php
		if($ouRound) {
?>		<tr>
<?php
/* COMBINED SCORE AND OVER UNDER RESULT --------------------------------------------------------------*/
			dbRestartQuery($result);  //put the results back to the start so we can interate over them again
			while($row = dbFetchRow($result)) {
			//This is an over or under guessing round, so we need to also show the over/under results
			$cs = $row['combined_score']+0.5;
				if(!(is_null($row['hscore']) || is_null($row['ascore']))) {
					$scores=$row['hscore']+$row['ascore'];
?>			<th class="score"><?php echo $cs;?></th>
			<th class="ou"><?php echo ($scores>$cs)?'Over':'Under';?></th>
<?php
				} else {
?>			<th class="score"><?php echo $cs;?></th><th></th>
<?php
				}
			}
?>		</tr>
<?php
		}
/* ADMINISTRATORS MATCH COMMENT IN HEADER ------------------------------------------------------------*/
		dbRestartQuery($result);  //put the results back to the start so we can interate over them again
?>		<tr>
<?php
		while($row = dbFetchRow($result)) {
?>			<th class="comment" colspan="2"><?php echo dbBBcode($row['comment']);?></td>
<?php
		}
?>		</tr>
<?php
	}
?>	</thead>
	<tbody>
<?php
/* FOR EACH USER WE DISPLAY THEIR PICKS FOR THE MATCHES -----------------------------------------------*/
	while ($userdata = dbFetchRow($resultuser)) {
?>			<tr>
				<td rowspan="<?php echo ($nom == 0)?1:2 ;?>" colspan="2"><?php echo $userdata['name'];?></td>
<?php
			$sql = 'SELECT p.hid, p.pid, p.over, p.comment, m.pscore,m.oscore';
			$sql .= ' FROM match_score m JOIN team t ON m.hid = t.tid LEFT JOIN pick p USING (cid,rid,hid,uid)';
			$sql .= ' WHERE m.cid = '.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid).' AND m.uid = '.$userdata['uid'];
			$sql .= ' ORDER BY t.confid, t.divid, m.hid LIMIT 8 OFFSET '.$startMatch.';';
			$pick = dbQuery($sql);

/* MATCH WINNER PICK ------------------------------------------------------------------------------*/
		if ($nom > 0) {
			dbRestartQuery($result);
			while ($row=dbFetchRow($result)) {
				$pickdata = dbFetchRow($pick);
				if(!is_null($pickdata['hid'])) {
					if($pickdata['pscore'] > 0) {
?>				<td class="win tid">
<?php
						echo $pickdata['pid'];tick();
					} else {
?>				<td class="tid">
<?php
						if (!is_null($pickdata['pid'])) echo $pickdata['pid'];
					}
?>				</td>
<?php
/* OVER UNDER PICK --------------------------------------------------------------------------------*/
					if($ouRound && !is_null($pickdata['over'])) {
						if($pickdata['oscore'] > 0) {
?>				<td class="win ou"><?php echo ($pickdata['over'] == 't')?'Over':'Under';tick();?></td>
<?php
						} else {
?>				<td class="ou"><?php echo ($pickdata['over'] == 't')?'Over':'Under';?></td>
<?php
						}
					} else {
?>				<td class="ou"></td>
<?php
					}
				} else {
?>				<td colspan="2"></td>
<?php
				}							
			}
		}
/* TOTAL SCORE (if we are doing it here) -------------------------------------------------------------*/
			if($totalHasBeenOutput) {
?>				<td class="score" rowspan="<?php echo ($nom == 0)?1:2 ;?>"><?php echo $userdata['score'];?></td>
<?php
			}
?>			</tr>
<?php
		if ($nom > 0) {
?>			<tr>
<?php
/* PICK COMMENT --------------------------------------------------------------------------------------*/
			dbRestartQuery($result);
			dbRestartQuery($pick);
			while ($row=dbFetchRow($result)) {
				$pickdata = dbFetchRow($pick);
	
			
?>				<td class="comment" colspan="2"><?php if(!( is_null($pickdata ['hid']) || is_null($pickdata['comment']))) echo dbBBcode($pickdata['comment']);?></td>
<?php
			}
?>			</tr>
<?php
			dbFree($pick);
		}
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
}


/* BONUS QUESTION --------------------------------------------------------------------------------------*/

if(!$totalHasBeenOutput) {
?><table>
	<thead>
		<tr><th rowspan="<?php echo ($isAQuestion)?3:1 ;?>" class="match_data">Round Data<br/><?php echo $rounddata['name'];?></th>
<?php		
	if($isAQuestion) {
	//We need to find all the options so we can display them later, but for now we need to know how many for the colspan
		$resultbq=dbQuery('SELECT * FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ORDER BY opid;');
		$bqopts = dbNumRows($resultbq);
		$width = (int) (500/$bqopts); // to style options
/* THE QUESTION IN HEADER -----------------------------------------------------------------------------*/
?>			<th <?php if($bqopts > 0) echo 'colspan="'.$bqopts.'"';?>>Bonus Question</th>
			<th class="score" rowspan="3">Bonus Score</th>
			<th class="score" rowspan="3">Pick Score</th>
<?php
	}
?>			<th class="score" rowspan="<?php echo ($isAQuestion)?3:1 ;?>">Total<br/>Round Score</th>
		</tr>
<?php
	if($isAQuestion) {
?>		<tr>
		<th class="question" <?php if($bqopts > 0) echo 'colspan="'.$bqopts.'"';?>><?php echo dbBBcode($rounddata['question']);?></th>
		</tr>
		<tr>
<?php
/* QUESTION OPTIONS IN HEADER --------------------------------------------------------------------------*/
		if($bqopts > 0) {
			// this is a multichoice question, so get results and output them
			while ($optdata = dbFetchRow($resultbq)) {
?>			<th style="width:<?php echo $width;?>px" <?php if(!is_null($rounddata['answer']) && $rounddata['answer'] == $optdata['opid']) {
					echo 'class="win opt_ans"';
				} else {
					echo 'class="opt_ans"';
				};?>><?php echo $optdata['label'];?></th>
<?php
			}
		} else {
?>			<th><?php echo $rounddata['answer'];?></th>
<?php
		}
?>		</tr>
<?php
	}
?>	</thead>
	<tbody>
<?php
	// User data was restarted further up (in case it was part of another group of matches)
	while ($userdata = dbFetchRow($resultuser)) {
/*  BONUS QUESTION ANSWERS ----------------------------------------------------------------------------------------*/
?>		<tr>
			<td rowspan="2"><?php echo $userdata['name'];?></td>
<?php
		if($isAQuestion) {
			if($bqopts > 0) {
				// this is a multichoice question, so get results and output them
				dbRestartQuery($resultbq);  //reset lost of options to start
				while ($optdata = dbFetchRow($resultbq)) {
					if($optdata['opid'] == $userdata['opid']) {
						if($userdata['opid'] == $rounddata['answer']) {
?>			<td class="win opt_sel"><img src="images/sel.gif" alt="option selected" /><?php tick();?></td>
<?php
						} else {
?>			<td class="opt_sel"><img src="images/sel.gif" alt="option selected" /></td>
<?php
						}
					
					} else {
?>			<td class="opt_sel"></td>
<?php
					}
				}
			} else{
				if($userdata['opid'] == $rounddata['answer']) {
?>			<td class="win score"><?php echo $userdata['opid'];tick();?></span></td>
<?php
				} else {
?>			<td class="score"><?php echo $userdata['opid'];?></td>
<?php
				}
			}
		}
/* BONUS, MATCH PICK and TOTAL SCORES --------------------------------------------------------------------------------*/
?>			<td rowspan="2" class="score"><?php echo $userdata['bscore'];?></td><td rowspan="2" class="score"><?php echo $userdata['mscore'];?></td>
			<td rowspan="2" class="score"><?php echo $userdata['score'];?></td>
		</tr>
<?php
/*  USER COMMENT TO THE QUESTION -----------------------------------------------------------------------*/
		if($isAQuestion) {
?>		<tr>
			<td class="comment" <?php if($bqopts > 0) echo 'colspan="'.$bqopts.'"';?>><?php echo dbBBcode($userdata['comment']);?></td>
		</tr>
<?php
		}
	}
?>	</tbody>
</table>
<?php
}
