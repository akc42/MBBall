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

require_once('./inc/bbcode.inc');


if ($rid != 0) {
  //Lets see if we can use the cache
  if(!is_null($rounddata['results_cache']) && ( MBBALL_CACHE_AGE == 0 || (($SERVER['REQUEST_TIME'] - $rounddata['cache_store_date']) < MBBALL_CACHE_AGE))) {
    //yes we can
    $usedCache = true;
    $picks = unserialize($rounddata['results_cache']);
  } else {
    $usedCache = false;
    $picks = Array();

    // we need to get the users resultant score
    $sql = 'SELECT name, u.uid AS uid , pscore, oscore, mscore,bscore,score, opid, comment';
    $sql .= ' FROM round_score r JOIN participant u USING (uid)';
    $sql .= ' LEFT JOIN option_pick p USING (cid,rid,uid)';
    $sql .= ' WHERE r.cid = ? AND r.rid = ? ORDER BY score DESC,u.name;';
    $u = $db->prepare($sql);
    $u->bindInt(1,$cid);
    $u->bindInt(2,$rid);
    $picks['users'] = Array();
    while($row = $u->fetchRow()){
      $picks['users'][] = $row;
    }
    unset($u);
    $m = $db->prepare("SELECT aid,hid,ascore,hscore,combined_score,match_time,comment,underdog FROM match WHERE cid = ? AND rid = ? AND open = 1 ORDER BY match_time, aid");
    $m->bindInt(1,$cid);
    $m->bindInt(2,$rid);
    $picks['matches'] = Array();
    while($row = $m->fetchRow()) {
      $picks['matches'][] = $row;
    }
    unset($m);
    $sql = "SELECT p.uid AS uid,p.aid AS aid,pid, over_selected, p.comment AS comment, pscore, oscore";
    $sql .= " FROM match_score m LEFT JOIN pick p USING (cid,rid,aid,uid)";
    $sql .= " WHERE m.cid = ? AND m.rid = ? ";
    $p = $db->prepare($sql);
    $p->bindInt(1,$cid);
    $p->bindInt(2,$rid);
    while($row = $p->fetchRow()) {
      $uid2 = $row['uid'];
      unset($row['uid']);
      $aid = $row['aid'];
      unset($row['uid']);
      $picks['picks'][$uid2][$aid] = $row;
    }
    unset($p);
    $o = $db->prepare("SELECT * FROM option WHERE cid = ? AND rid = ? ");
    $o->bindInt(1,$cid);
    $o->bindInt(2,$rid);
    while($row = $o->fetchRow()) {
      $picks['options'][$row['opid']] = $row['label'];
    }
    unset($o);
    $r = $db->prepare("UPDATE round SET results_cache = ?, cache_store_date = (strftime('%s','now')) WHERE cid = ? AND rid = ?");
    $r->bindString(1,serialize($picks));
    $r->bindInt(2,$cid);
    $r->bindInt(3,$rid);
    $r->exec();
    unset($r);
  }
?><h1>Details of this rounds pick<?php if(!$usedCache) echo '<span class="without_cache">&nbsp;</span>';?></h1>
<?php

  $nom = count($picks['matches']);
  $isAQuestion = ($rounddata['valid_question'] == 1);
  $ouRound = ($rounddata['ou_round'] == 1);
  $totalHasBeenOutput = false;
  $startMatch = 0;
  $bqopts = 0;
  if (isset($picks['options'])) $bqopts = count($picks['options']);
	
  while ($startMatch < $nom) {
/* GROUPS OF 8 MATCHES ----------------------------------------------------------------------------*/
?><table>
	<thead>
		<tr>
			<th rowspan="<?php echo (($ouRound)?5:4) ;?>" class="match_data">Match Data<br/><?php echo $rounddata['name'];?></th>
			<th rowspan="2" class="score">Points for<br/>Correct Pick</th>
<?php
/* TEAM NAMES IN HEADER ---------------------------------------------------------------*/
    for($i = $startMatch;($i<$startMatch+8) && $i<$nom;$i++){
      $row = $picks['matches'][$i];
?>			<th colspan="2" class="tid">
<?php
      if(!(is_null($row['ascore']) || is_null($row['hscore']) || $row['ascore'] < $row['hscore'])) {
	//Away win
?>				<span class="win aid"><?php tick();
      } else {
?>				<span class="aid"><?php
      }
      echo $row['aid']; ?><span>@
<?php
      if(!(is_null($row['ascore']) || is_null($row['hscore']) || $row['ascore'] > $row['hscore'])) {
      //Home win
?>				<span class="win hid"><?php echo $row['hid']; tick()?></span>
<?php
      } else {
?>				<span class="hid"><?php	echo $row['hid'];?></span>
<?php
      }
?>			</th>
<?php
    }
    // if we have less than eight matches left and there is only the overall total to output we should do it rather than create a new table
    if($nom-$startMatch < 8 && !$isAQuestion) {
      $totalHasBeenOutput = true;
?>			<th rowspan="<?php echo ($nom==0)?2:(($ouRound)?5:4) ;?>" class="score">Round Score</th>
<?php
    }
?>		</tr>
		<tr>
<?php
/*  MATCH TIME ROW IN HEADER --------------------------------------------------------------------------*/
    for($i = $startMatch;($i<$startMatch+8) && $i<$nom;$i++){
      $row = $picks['matches'][$i];
?>			<th colspan="2"><span class="time"><?php echo $row['match_time'];?></span></th>
<?php
    }
/* THE VALUE OF A CORRECT PICK ----------------------*/
?>		</tr>
		<tr>
			<th class="score" rowspan="<?php echo ($nom==0)?1:(($ouRound)?3:2) ;?>"><?php echo $rounddata['value'];?></th>
<?php
/* THE SCORES IN HEADER ------------------------------------------------------------------------------*/
    for($i = $startMatch;($i<$startMatch+8) && $i<$nom;$i++){
      $row = $picks['matches'][$i];
?>			<th class="score"><div style="position:relative;"><?php echo is_null($row['ascore'])?'&nbsp;':$row['ascore']; if ($row['underdog'] < 0) echo '<div class="xtra">'.(-$row['underdog']).'</div>';?></div></th>
			<th class="score"><div style="position:relative;"><?php echo is_null($row['hscore'])?'&nbsp;':$row['hscore']; if ($row['underdog'] > 0) echo '<div class="xtra">'.($row['underdog']).'</div>';?></div></th>
<?php
    }
?>		</tr>
<?php
    if($ouRound) {
?>		<tr>
<?php
/* COMBINED SCORE AND OVER UNDER RESULT --------------------------------------------------------------*/
      for($i = $startMatch;($i<$startMatch+8) && $i<$nom;$i++){
	$row = $picks['matches'][$i];
      //This is an over or under guessing round, so we need to also show the over/under results
	$cs = $row['combined_score']+0.5;
	if(!(is_null($row['ascore']) || is_null($row['hscore']))) {
	  $scores=$row['ascore']+$row['hscore'];
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
?>		<tr>
<?php
    for($i = $startMatch;($i<$startMatch+8) && $i<$nom;$i++){
      $row = $picks['matches'][$i];
?>			<th class="comment" colspan="2"><?php echo dbBBcode($row['comment']);?></td>
<?php
    }
?>		</tr>
	</thead>
	<tbody>
<?php
/* FOR EACH USER WE DISPLAY THEIR PICKS FOR THE MATCHES -----------------------------------------------*/
    foreach($picks['users'] as $userdata) {
?>			<tr>
				<td rowspan="<?php echo ($nom == 0)?1:2 ;?>" colspan="2"><?php echo $userdata['name'];?></td>
<?php
      $uid2 = $userdata['uid'];
/* MATCH WINNER PICK ------------------------------------------------------------------------------*/
      for($i = $startMatch;($i<$startMatch+8) && $i<$nom;$i++){
	$aid = $picks['matches'][$i]['aid'];
	$underdog = $picks['matches'][$i]['underdog'];
	if(isset($picks['picks'][$uid2][$aid])) {
	  $pickdata = $picks['picks'][$uid2][$aid]; 
	  if($pickdata['pscore'] > 0) {
?>				<td class="win tid"><div style="position:relative;">
<?php
	    echo $pickdata['pid'];tick();
	  } else {
?>				<td class="tid"><div style="position:relative;">
<?php
	    if (!is_null($pickdata['pid'])) echo $pickdata['pid'];
	  }
	  //indicate an underdog pick if there was one
	  if ($underdog < 0 && $pickdata['pid'] == $aid) echo '<div class="xtra">'.(-$row['underdog']).'</div>';
	  if ($underdog > 0 && $pickdata['pid'] <> $aid) echo '<div class="xtra">'.($row['underdog']).'</div>';
?>				</div></td>
<?php
/* OVER UNDER PICK --------------------------------------------------------------------------------*/
	  if($ouRound && !is_null($pickdata['over_selected'])) {
	    if($pickdata['oscore'] > 0) {
?>				<td class="win ou"><?php echo ($pickdata['over_selected'] == 1)?'Over':'Under';tick();?></td>
<?php
	    } else {
?>				<td class="ou"><?php echo ($pickdata['over_selected'] == 1)?'Over':'Under';?></td>
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
      for($i = $startMatch;($i<$startMatch+8) && $i<$nom;$i++){
	$aid = $picks['matches'][$i]['aid'];

?>				<td class="comment" colspan="2"><?php if(isset($picks['picks'][$uid2][$aid]) && !is_null($picks['picks'][$uid2][$aid]['comment'])) echo dbBBcode($picks['picks'][$uid2][$aid]['comment']);?></td>
<?php
      }
?>			</tr>
<?php
    }
?>	</tbody>
</table>
<?php
    $startMatch += 8;
    $nom -= 8;
  }
  unset($p);
  unset($m);
  unset($pickdata);

/* BONUS QUESTION --------------------------------------------------------------------------------------*/
  if(!$totalHasBeenOutput) {
?><table>
	<thead>
		<tr><th rowspan="<?php echo ($isAQuestion)?3:1 ;?>" class="match_data">Round Data<br/><?php echo $rounddata['name'];?></th>
<?php		
    if($isAQuestion) {
      //We need to find all the options so we can display them later, but for now we need to know how many for the colspan
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
	  foreach($picks['options'] as $opid => $label) {
	    if(!is_null($rounddata['answer']) && $rounddata['answer'] == $opid) {
?>			<th style="<?php echo 'width:'.$width.'px';?>" class="win opt_ans"><?php echo $label; tick();?></th>
<?php 
	    } else {
?> 			<th style="<?php echo 'width:'.$width.'px';?>" class="opt_ans"><?php echo $label;?></th>
<?php 		
	    }
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
    foreach($picks['users'] as $userdata) {
/*  BONUS QUESTION ANSWERS ----------------------------------------------------------------------------------------*/
?>		<tr>
			<td rowspan="<?php echo ($isAQuestion)?2:1 ;?>"><?php echo $userdata['name'];?></td>
<?php
      if($isAQuestion) {
	if($bqopts > 0) {
	  // this is a multichoice question, so get results and output them
	  foreach($picks['options'] as $opid => $label) {
	    if($opid == $userdata['opid']) {
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
  unset($picks);
  unset($rounddata['results_cache']);
} else {
?>
<h1>Details of this rounds pick</h1>
<p>There are no rounds in this competition to display right now.  Please come back later</p>
<?php
}
?>
