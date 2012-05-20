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

require_once('./bbcode.inc');

// we need to get the users resultant score
$sql = 'SELECT u.name , u.uid , r.pscore, r.oscore, r.mscore,r.bscore,r.score, p.opid, p.comment';
$sql .= ' FROM round_score r JOIN participant u USING (uid)';
$sql .= ' LEFT JOIN option_pick p USING (cid,rid,uid)';
$sql .= ' WHERE cid = ? AND rid = ? ORDER BY score DESC,u.name;';
$u = $db->prepare($sql);


$isAQuestion = ($rounddata['valid_question'] == 1);
$ouRound = ($rounddata['ou_round'] == 1);
$totalHasBeenOutput = false;
$moreMatchesToCome = true;
$startMatch = 0;
?><h1>Details of this rounds pick</h1>
<?php
if ($rid != 0) {
	$u->bindInt(1,$cid);
	$u->bindInt(2,$rid);
	$u->exec();
/* GROUPS OF 8 MATCHES ----------------------------------------------------------------------------*/
while ($moreMatchesToCome) {
	$m = $db->prepare("SELECT * FROM match WHERE .cid = ? AND rid = ? AND m.open = 1 ORDER BY match_time, hid, LIMIT 8 OFFSET ?");
	$m->bindInt(1,$cid);
	$m->bindInt(2,$rid);
	$m->bindInt(2,$startMatch);
	$m->exec();
	$do_first = true;
	while($row = $m->fetch()) {
		if(do_first) {	
?><table>
	<thead>
		<tr>
			<th rowspan="<?php echo (($ouRound)?5:4) ;?>" class="match_data">Match Data<br/><?php echo $rounddata['name'];?></th>
			<th rowspan="2" class="score">Points for<br/>Correct Pick</th>
<?php
/* TEAM NAMES IN HEADER ---------------------------------------------------------------*/
		}
		$do_first = false;
?>			<th colspan="2" class="tid">
<?php
		if(!(is_null($row['hscore']) || is_null($row['ascore']) || $row['hscore'] < $row['ascore'])) {
			//Home win
?>				<span class="win hid"><?php tick();
		} else {
?>				<span class="hid"><?php
		}
		echo $row['hid']; ?><span>@
<?php
		if(!(is_null($row['hscore']) || is_null($row['ascore']) || $row['hscore'] > $row['ascore'])) {
			//Away win
?>				<span class="win aid"><?php echo $row['aid']; tick()?></span>
<?php
		} else {
?>				<span class="aid"><?php	echo $row['aid'];?></span>
<?php
		}
?>			</th>
<?php
	}

?>			
<?php
	
		// if we have less than eight matches left and there is noly the overall total to output we should do it rather than create a new table
		if($nom < 8 && !$isAQuestion) {
			$totalHasBeenOutput = true;
?>			<th rowspan="<?php echo ($nom==0)?2:(($ouRound)?5:4) ;?>" class="score">Round Score</th>
<?php
		}
?>		</tr>
<?php
	
?>		<tr>
<?php
/*  MATCH TIME ROW IN HEADER --------------------------------------------------------------------------*/
			//first three rows first column covered by rowspan
		dbRestartQuery($result);  //put the results back to the start so we can interate over them again
		while($row = dbFetchRow($result)) {
?>			<th colspan="2"><span class="time"><?php echo $row['match_time'];?></span></th>
<?php
		}
	
/* THE VALUE OF A CORRECT PICK ----------------------*/
?>		</tr>
		<tr>
			<th class="score" rowspan="<?php echo ($nom==0)?1:(($ouRound)?3:2) ;?>"><?php echo $rounddata['value'];?></th>
<?php
/* THE SCORES IN HEADER ------------------------------------------------------------------------------*/
	
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
	
?>	</thead>
	<tbody>
<?php
/* FOR EACH USER WE DISPLAY THEIR PICKS FOR THE MATCHES -----------------------------------------------*/
		while ($userdata = $u->fetch()) {
?>			<tr>
				<td rowspan="<?php echo ($nom == 0)?1:2 ;?>" colspan="2"><?php echo $userdata['name'];?></td>
<?php
			$sql = 'SELECT p.hid, p.pid, p.over_selected, p.comment, m.pscore,m.oscore';
			$sql .= ' FROM match_score m JOIN match USING (cid,rid,hid) LEFT JOIN pick p USING (cid,rid,hid,uid)';
			$sql .= ' WHERE m.cid = '.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid).' AND m.uid = '.$userdata['uid'];
			$sql .= ' ORDER BY match.match_time , m.hid LIMIT 8 OFFSET '.$startMatch.';';
			$pick = dbQuery($sql);

/* MATCH WINNER PICK ------------------------------------------------------------------------------*/
		
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
					if($ouRound && !is_null($pickdata['over_selected'])) {
						if($pickdata['oscore'] > 0) {
?>				<td class="win ou"><?php echo ($pickdata['over_selected'] == 't')?'Over':'Under';tick();?></td>
<?php
						} else {
?>				<td class="ou"><?php echo ($pickdata['over_selected'] == 't')?'Over':'Under';?></td>
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
		
/* TOTAL SCORE (if we are doing it here) -------------------------------------------------------------*/
			if($totalHasBeenOutput) {
?>				<td class="score" rowspan="<?php echo ($nom == 0)?1:2 ;?>"><?php echo $userdata['score'];?></td>
<?php
			}
?>			</tr>
<?php

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
		if ($bqopts > 0) $width = (int) (500/$bqopts); // to style options
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
					echo 'class="win opt_ans">'.$optdata['label']; tick();
				} else {
					echo 'class="opt_ans">'.$optdata['label'];
				}
?>          </th>
<?php
			}
		} else {
?>			<th class="single_answer"><?php echo (is_null($rounddata['answer']))?'<i>No Answer Available Yet</i>':$rounddata['answer'];?></th>
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
			<td rowspan="<?php echo ($isAQuestion)?2:1 ;?>"><?php echo $userdata['name'];?></td>
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
				if(!is_null($rounddata['answer']) && $userdata['opid'] == $rounddata['answer']) {
?>			<td class="win"><?php echo $userdata['opid'];tick();?></span></td>
<?php
				} else {
?>			<td class="single_answer"><?php echo $userdata['opid'];?></td>
<?php
				}
			}
?>			<td rowspan="2" class="score"><?php echo $userdata['bscore'];?></td><td rowspan="2" class="score"><?php echo $userdata['mscore'];?></td>
<?php
		}
/* BONUS, MATCH PICK and TOTAL SCORES --------------------------------------------------------------------------------*/
?>			<td rowspan="<?php echo ($isAQuestion)?2:1 ;?>" class="score"><?php echo $userdata['score'];?></td>
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
} else {
?><p>There are no rounds in this competition to display right now.  Please come back later</p>
<?php
}
?>