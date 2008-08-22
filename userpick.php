<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if (!defined('BALL'))
	die('Hacking attempt...');
$time_at_top = time();
// If user is registered and we can do picks then we need to display the  Picks Section
$sql = 'SELECT m.hid , m.aid , p.pid , m.combined_score AS cs, p.over , p.comment AS comment, m.comment AS adm_comment, m.match_time';
$sql .= ' FROM match m LEFT JOIN pick p ';
$sql .= 'ON m.cid = p.cid AND m.rid = p.rid AND m.hid = p.hid AND p.uid = '.dbMakeSafe($uid);
$sql .= ' WHERE m.cid = '.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid).' AND m.open IS TRUE AND m.match_time > '.dbMakeSafe($time_at_top +$gap);
$sql .= ' ORDER BY m.match_time, m.hid;';
$result = dbQuery($sql);
$nomatches = dbNumRows($result);

if ($nomatches > 0 || $rounddata['valid_question']||($playoff_deadline != 0 and $playoff_deadline > $time_at_top)) {
?><form id="pick">
	<input type="hidden" name="uid" value="<?php echo $uid;?>" />
	<input type="hidden" name="pass" value="<?php echo $password;?>" />
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
	<input type="hidden" name="rid" value="<?php echo $rid;?>" />
	<input type="hidden" name="gap" value="<?php echo $gap;?>" />
	<input type="hidden" name="ppd" value="<?php echo $playoff_deadline ;?>" />
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
								<th class="comment">Players Comment</td>
							</tr>
						</thead>
						<tbody>
<?php
		while($row=dbFetchRow($result)) {
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

<?php
			if($row['match_time']< $time_at_top +$gap+86400) { //less than a day before pick limit
			
?>		 									<tr>
												<th colspan="2" class="limited">Less than a DAY to pick</th>
											</tr>
											
<?php
			}
?>										</thead>
										<tbody>
											<tr>
												<td><input	type="radio"
													name="<?php echo 'M'.$row['hid'];?>"
													value="<?php echo $row['hid'];?>"
													<?php if ($row['pid'] == $row['hid']) echo 'checked';?>/></td>
												<td><input	type="radio"
													name="<?php echo 'M'.$row['hid'];?>"
													value="<?php echo $row['aid'];?>"
											 		<?php if ($row['pid'] == $row['aid']) echo 'checked';?>/></td>
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
														<?php if ($row['over'] == 'f') echo 'checked';?>/></td>
												<td><input	type="radio"
														name="<?php echo 'O'.$row['hid'];?>"
														value="O"
														<?php if ($row['over'] == 't') echo 'checked';?>/></td>
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
	}	dbFree($result);
?>				</td>
				<td id="instructions"><?php require_once('instructions.html');?></td>
			</tr>
			<tr>
				<td id="bonus_pick">
<?php	
	if ($rounddata['valid_question']) {
?>					<table>
						<caption>Bonus Question</caption>
						<thead>
							<tr><th class="bq">Question</th><th class="ba">Answer</th><th class="comment">Comment</th></tr>
						</thead>
						<tbody>
<?php
		$result=dbQuery('SELECT * FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ORDER BY opid;');
		$noopts = dbNumRows($result);  //No of multichoice examples 0= numeric answer required
		$resultop = dbQuery('SELECT * FROM option_pick WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid)
				.' AND uid = '.dbMakeSafe($uid).'ORDER BY opid;');
		$optdata = dbFetchRow($resultop); //even on multichoice there is only going to be one pick

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

?>								<td><input type="radio" name="opid" value="<?php echo $row['opid'];?>" <?php if((int)$optdata['opid'] == (int)$row['opid']) echo 'checked';?>	/><?php echo $row['label'];?></td>
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
	} // end valid question check
	dbFree($result);
	dbFree($resultop);
?>				</td>
			</tr>
			<tr>
				<td><?php require_once('emoticons.php'); ?></td>
			</tr>
<?php
	if($playoff_deadline != 0 and $playoff_deadline > $time_at_top) {
		require_once('team.php');
//Playoff selection is part of this competition
?>			<tr>
				<td id="playoff_pick" colspan="2">
<?php
$result=dbQuery('SELECT tid,divid,confid FROM div_winner_pick WHERE cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($uid).';');
		$dw = array(array());
		while($row = dbFetchRow($result)) {
			$dw[$row['confid']][$row['divid']] = $row['tid'];
		}
dbFree($result);
$result=dbQuery('SELECT tid,opid,confid FROM wildcard_pick WHERE cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($uid).';');
		$wild = array(array());
		while($row = dbFetchRow($result)) {
			$wild[$row['confid']][$row['opid']] = $row['tid'];
		}
?>					<table>
						<caption>Pick divisional winner and wildcard picks for each conference</caption>
						<thead>
							<tr>
								<th class="confhead">\</th><th class="confhead">Division</th>
<?php
		foreach($divs as $division) {
			// for each division we are building a team id, name and logo columns
?>								<th class="t_dn" colspan="4"><?php echo $division;?></th>
<?php
		}
?>							</tr>		
							<tr>
								<th class="confhead">Conference</th><th class="confhead">\</th>
<?php
		foreach($divs as $division) {
?>								<th class="tid">Team</th>
								<th class="radio">D Win</th>
								<th class="radio">Wild1</th>
								<th class="radio">Wild2</th>
<?php
		}
?>							</tr>	
						</thead>
						<tbody>
<?php
		foreach($confs as $confid => $conference) {
			$no_of_rows = max($sizes[$confid]);
			$w1tid = (isset($wild[$confid][1]))?$wild[$confid][1]:'';
			$w2tid = (isset($wild[$confid][2]))?$wild[$confid][2]:'';
?>							<tr>
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
											<?php if ($dwtid == $tid) echo 'checked';?> /></td>
								<td class="radio">
									<input type="radio" class="ppick" 
											name="<?php echo 'W1'.$confid;?>"
											value="<?php echo $tid;?>"
											<?php if ($w1tid == $tid) echo 'checked'; ?> /></td>
								<td class="radio">
									<input type="radio" class="ppick" 
											name="<?php echo 'W2'.$confid;?>"
											value="<?php echo $tid;?>"
											<?php if ($w2tid == $tid) echo 'checked';?> /></td>
<?php
					} else {
?>								<td colspan="4"></td>	
<?php
					}
				}
?>							</tr>
<?php
			}
		}
?>						</tbody>
					</table>
				</td>
			</tr>
<?php
	}  //playoffs
?>
		</tbody>
	</table>
	<input id="make_picks" type="submit" value="Make Picks" />
</form>
<?php
}