<?php
/*
 	Copyright (c) 2008,2009,2010 Alan Chandler
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

$time_at_top = time();
$roundpicks = false;
$playpicks = false;
if ($rid != 0) {
    // If user is registered and we can do picks then we need to display the  Picks Section
    $result = $db->prepare("
    	SELECT count(*) FROM match m LEFT JOIN pick p ON m.rid = p.rid AND m.hid = p.hid AND p.uid = ?
    	WHERE m.rid = ? AND m.open =1 AND m.match_time > ?
    	ORDER BY m.match_time, m.hid;
    ");
    $result->bindValue(1,$uid,PDO::PARAM_INT);
    $result->bindValue(2,$rid,PDO::PARAM_INT);
    $result->bindValue(3,$time_at_top+$gap);
    $result->execute();
    $nomatches = $result->fetchColumn();
    $result->closeCursor();
    $result = $db->prepare("
    	SELECT m.hid , m.aid , p.pid , m.combined_score AS cs, p.over_selected , p.comment AS comment, m.comment AS adm_comment, m.match_time
    	FROM match m LEFT JOIN pick p ON m.rid = p.rid AND m.hid = p.hid AND p.uid = ?
    	WHERE m.rid = ? AND m.open =1 AND m.match_time > ?
    	ORDER BY m.match_time, m.hid;
    ");
    $result->bindValue(1,$uid,PDO::PARAM_INT);
    $result->bindValue(2,$rid,PDO::PARAM_INT);
    $result->bindValue(3,$time_at_top+$gap);
    $result->execute();
    if ($nomatches > 0 || ($rounddata['valid_question'] && $rounddata['deadline'] > $time_at_top) ||($playoff_deadline != 0 && $playoff_deadline > $time_at_top)) {
        $roundpicks = true;
?><form id="pick">
	<input type="hidden" name="uid" value="<?php echo $uid;?>" />
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
	<input type="hidden" name="rid" value="<?php echo $rid;?>" />
	<input type="hidden" name="gap" value="<?php echo $gap;?>" />
	<input type="hidden" name="ppd" value="<?php echo $playoff_deadline ;?>" />
	<input type="hidden" name="bqdeadline" value="<?php echo $rounddata['deadline'];?>" />
	<table class="layout">
		<tbody>
			<tr>	
				<td id="picks" rowspan="3">
<?php
    	if($nomatches >0) {
?>					<table>
						<caption>Match Picks for <?php echo $rounddata['name'];?></caption>
						<thead>
							<tr>
								<th class="team">Team Pick</th>
<?php
    		if ($rounddata['ou_round'] == 't') {
?>								<th class="score">Combined Score</th>
<?php
    		}
?>								<th class="comment">Administrators Comment</td>
								<th class="comment">Pickers Comment</td>
							</tr>
						</thead>
						<tbody>
<?php
		    while($row=$result->fetch(PDO::FETCH_ASSOC)) {
			    $row['hid'] = trim($row['hid']);
			    $row['aid'] = trim($row['aid']);
			    $row['pid'] = trim($row['pid']);
?>							<tr>
								<td>
									<table>
										<thead>
											<tr>
												<th colspan="2">
													<span class="hid"><?php echo $row['hid'];?></span>@<span class="aid"><?php echo $row['aid'];?></span>
												</th>
											</tr>
											<tr>
												<th colspan="2" class="mtime"><span class="time"><?php echo $row['match_time']; ?></span></th>
											</tr>											
										</thead>
										<tbody>
											<tr>
												<td><input	type="radio"
													name="<?php echo 'M'.$row['hid'];?>"
													value="<?php echo $row['hid'];?>"
													<?php if ($row['pid'] == $row['hid']) echo 'checked="checked"';?>/></td>
												<td><input	type="radio"
													name="<?php echo 'M'.$row['hid'];?>"
													value="<?php echo $row['aid'];?>"
											 		<?php if ($row['pid'] == $row['aid']) echo 'checked="checked"';?>/></td>
											</tr>
		 									<tr>
												<td colspan="2" <?php if (($row['match_time'] - $gap)< ($time_at_top+86400)) echo 'class="limited"' ;?> >
													<span class="time"><?php echo $row['match_time'] - $gap;?></span>
<?php
    			if (($row['match_time'] - $gap)< ($time_at_top+86400)) {
?>													<span>Less than a DAY to pick</span>
<?php
    			 }
?>												</td>
											</tr>
										</tbody>
									</table>
								</td>
<?php
    			if ($rounddata['ou_round'] == 't') {
?>								<td>
									<table>
										<tbody>
											<tr>
												<td colspan="2" class="score"><?php echo ($row['cs']+0.5);?></td>
											</tr>
											<tr>
												<td class="ou">Under</td><td class="ou">Over</td>
											</tr>
											<tr>
												<td><input	type="radio"
														name="<?php echo 'O'.$row['hid'];?>"
														value="U"
														<?php if ($row['over_selected'] == 0) echo 'checked="checked"';?>/></td>
												<td><input	type="radio"
														name="<?php echo 'O'.$row['hid'];?>"
														value="O"
														<?php if ($row['over_selected'] == 1) echo 'checked="checked"';?>/></td>
											</tr>
										</tbody>
									</table>
								</td>
<?php
    			}
?>								<td class="comment"><?php echo dbBBcode($row['adm_comment']);?></td>
								<td><textarea name="<?php echo 'C'.$row['hid'];?>" class="comment" rows="2" cols="20"><?php echo $row['comment'];?></textarea></td>
							</tr>
<?php
    		}
?>						</tbody>
					</table>
<?php
    	}	
    	$result->closeCursor();
?>				</td>
				<td id="instructions"><?php require_once('./inc/instructions.inc');?></td>
			</tr>
			<tr>
				<td id="emoticon_cell"><?php require_once('./inc/emoticons.inc'); ?></td>
			</tr>
			<tr>
				<td id="bonus_pick">
<?php	
    	if ($rounddata['valid_question'] && $rounddata['deadline'] > $time_at_top) {
?><h1>Bonus Question</h1>
<h2>(Deadline: <span class="time"><?php echo $rounddata['deadline'];?></span>)</h2>
<?php
    		if ($rounddata['deadline'] < ($time_at_top+86400)) {
?><h2 class="limited">Less than a DAY to answer</h2>
<?php
    		 }
?>
					<table>
						<thead>
							<tr><th class="bq">Question</th><th class="ba">Answer</th><th class="comment">Comment</th></tr>
						</thead>
						<tbody>
<?php
		    $result=$db->prepare("SELECT count(*) FROM option WHERE rid = ? ORDER BY opid;");
		    $result->bindValue(1,$rid,PDO::PARAM_INT);
		    $result->execute();
		    $noopts = $result->fetchColumn();
		    $result->closeCursor();
		    $result=$db->prepare("SELECT * FROM option WHERE rid = ? ORDER BY opid;");
		    $result->bindValue(1,$rid,PDO::PARAM_INT);
			$resultop = $db->prepare("SELECT * FROM option_pick WHERE rid = ? AND uid = ? ORDER BY opid;");
			$resultop->bindValue(1,$rid,PDO::PARAM_INT);
			$resultop->bindValue(2,$uid,PDO::PARAM_INT);
			$resultop->execute();
		    $optdata = $resultop->fetch(PDO::FETCH_ASSOC); //even on multichoice there is only going to be one pick

		    if ($noopts > 0) {
    //Question is multichoice
			    for($i=1; $i<=$noopts;$i++) {
    //We get one loop round anyway if noopts = 0
				    $row = dbFetchRow($result);


?>							<tr>
<?php
    				if($i == 1) {
?>								<td class="question" rowspan ="<?php echo $noopts ;?>"><?php echo dbBBcode($rounddata['question']);?></td>
								
<?php
    				}

?>								<td><input type="radio" name="opid" value="<?php echo $row['opid'];?>" <?php if((int)$optdata['opid'] == (int)$row['opid']) echo 'checked="checked"';?>	/><?php echo $row['label'];?></td>
<?php

				    if($i == 1) {
?>								<td rowspan ="<?php echo $noopts ;?>">
<textarea class="comment" name="Cbonus"><?php echo $optdata['comment'];?></textarea>
								</td>
<?php
    				}
?>							</tr>
<?php
			    }
		    } else {
?>								<td class="question" rowspan ="<?php echo $noopts ;?>"><?php echo dbBBcode($rounddata['question']);?></td>
								<td><input id="answer" type="text" name="opid" value="<?php	echo $optdata['opid'];?>"	/></td>
								<td rowspan ="<?php echo $noopts ;?>">
<textarea class="comment" name="Cbonus"><?php echo $optdata['comment'];?></textarea>
								</td>
<?php
    		}
?>						</tbody>
					</table>
<?php
		    $resultop->closeCursor();
		    $result->closeCursor();
	    } // end valid question check
?>				</td>
			</tr>
<?php
    }
} // end $rid !=0 
if($playoff_deadline != 0 && $playoff_deadline > $time_at_top) {
    $playpicks = true;
    if(!$roundpicks) { // need to do this because didn't do it at top
?><form id="pick">
	<input type="hidden" name="uid" value="<?php echo $uid;?>" />
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
	<input type="hidden" name="rid" value="<?php echo $rid;?>" />
	<input type="hidden" name="gap" value="<?php echo $gap;?>" />
	<input type="hidden" name="ppd" value="<?php echo $playoff_deadline ;?>" />
	<table class="layout">
		<tbody>
<?php
    }
	require_once('./inc/team.inc');
    //Playoff selection is part of this competition
?>			<tr>
				<td id="playoff_pick" colspan="2">
<?php
	$result = $db->prepare("SELECT tid,divid,confid FROM div_winner_pick WHERE uid = ?;");
	$result->bindValue(1,$uid,PDO::PARAM_INT);
	$result->execute();
	$dw = array(array());
	while($row = $result->fetch(PDO::FETCH_ASSOC)) {
		$dw[$row['confid']][$row['divid']] = trim($row['tid']);
	}
    $result->closeCursor();
    $result=$db->prepare("SELECT tid,opid,confid FROM wildcard_pick WHERE uid = ? ;");
    $result->bindValue(1,$uid,PDO::PARAM_INT);
    $result->execute();
	$wild = array(array());
	while($row = $result->fetch(PDO::FETCH_ASSOC)) {
		$wild[$row['confid']][$row['opid']] = trim($row['tid']);
	}
	$result->closeCursor();
?><h1>Pick divisional winner and wildcard picks for each conference</h1>
<h2>Deadline : <span class="time"><?php echo $playoff_deadline;?></span><?php
	if ($playoff_deadline< ($time_at_top+86400)) {
?><span>Less than a DAY to pick</span><?php
	 }
?></h2>
<?php
	foreach($confs as $confid => $conference) {
		$no_of_rows = max($sizes[$confid]);
		$w1tid = (isset($wild[$confid][1]))?$wild[$confid][1]:'';
		$w2tid = (isset($wild[$confid][2]))?$wild[$confid][2]:'';
?><table>
	<thead>
		<tr>
			<th class="confhead">\</th><th class="confhead">Division</th>

<?php
		foreach($divs as $divid => $division){
?>			<th colspan="4">
				<?php echo $division; ?></th>
<?php
		}

?>
		</tr>
		<tr>
			<th class="confhead">Conference</th><th class="confhead">\</th>
<?php
		foreach($divs as $division) {
?>			<th class="tid">Team</th>
			<th class="radio">D Win</th>
			<th class="radio">Wild1</th>
			<th class="radio">Wild2</th>
<?php
		}
?>							</tr>	

	</thead>
	<tbody>
		<tr>
			<td class="conference" colspan="2" rowspan="<?php echo $no_of_rows;?>"><?php echo $conference;?></td>
<?php
		for ($i = 0; $i < $no_of_rows;$i++) {
			if( $i != 0) {
?>							<tr>
<?php
			}
			foreach($divs as $divid => $division) {
				if(isset($teams[$confid][$divid][$i])) { //only if this entry is set
					$tid=$teams[$confid][$divid][$i]['tid'];
					$dwtid = (isset($dw[$confid][$divid]))?$dw[$confid][$divid]:'';;
?>								<td class="tid"><?php echo $tid; ?></td>
								<td class="radio">
									<input type="radio" class="ppick" 
											name="<?php echo 'D'.$divid.$confid;?>"
											value="<?php echo $tid;?>"
											<?php if ($dwtid == $tid) echo 'checked="checked"';?> /></td>
								<td class="radio">
									<input type="radio" class="ppick" 
											name="<?php echo 'W1'.$confid;?>"
											value="<?php echo $tid;?>"
											<?php if ($w1tid == $tid) echo 'checked="checked"'; ?> /></td>
								<td class="radio">
									<input type="radio" class="ppick" 
											name="<?php echo 'W2'.$confid;?>"
											value="<?php echo $tid;?>"
											<?php if ($w2tid == $tid) echo 'checked="checked"';?> /></td>
<?php
				} else {
?>								<td colspan="4"></td>	
<?php
				}
			}
?>							</tr>
<?php
		}
?>						</tbody>
					</table>
<?php
	}
?>				</td>
			</tr>
<?php
}  //playoffs
if ($roundpicks || $playpicks) { //we need to finish off if either of these
?>

		</tbody>
	</table>
	<button id="make_picks">Make Your Picks</div>
</form>
<?php
}
?>
