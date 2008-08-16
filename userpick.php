<?php
if (!defined('BALL'))
	die('Hacking attempt...');

// If user is registered and we can do picks then we need to display the  Picks Section
$sql = 'SELECT m.hid , m.aid , p.pid , m.combined_score AS cs, p.over , p.comment ';
$sql .= ' FROM match m JOIN team t ON m.hid = t.tid LEFT JOIN pick p ';
$sql .= 'ON m.cid = p.cid AND m.rid = p.rid AND m.hid = p.hid AND p.uid = '.dbMakeSafe($uid);
$sql .= ' WHERE m.cid = '.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid).' AND m.open IS TRUE AND m.match_time > '.dbMakeSafe(time()+$gap);
$sql .= ' ORDER BY t.confid,t.divid, m.hid;';
$result = dbQuery($sql);
$nomatches = dbNumRows($result);
$time_at_top = time();
if ($nomatches > 0 || $rounddata['valid_question']||($playoff_deadline != 0 and $play_off_deadline > $time_at_top)) {
?><form id="pick">
	<input type="hidden" name="uid" value="<?php echo $uid;?>" />
	<input type="hidden" name="pass" value="<?php echo $password;?>" />
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
	<input type="hidden" name="rid" value="<?php echo $rid;?>" />		
	<table class="layout">
		<tbody>
			<tr>	
				<td id="picks" rowspan="2">
<?php
	if($nomatches >0) {
?>					<table>
						<caption>Match Picks for <?php echo $rounddata['name'];?></caption>
						<thead>
							<tr>
								<th class="team">Team Pick</th>
<?php
		if ($rounddata['ou_round'] == 't') {
?>								<th class="score">Score</th><th class="ou_select">Over/Under</th>
<?php
		}
?>
								<th class="comment">Comment</td>
								</tr>
						</thead>
						<tbody>
<?php
		while($row=dbFetchRow($result)) {
?>							<tr>
								<td>
									<input	type="radio"
											name="<?php echo 'M'.$row['hid'];?>"
											value="<?php echo $row['hid'];?>"
											<?php if ($row['pid'] == $row['hid']) echo 'checked';?>/>
										<?php echo $row['hid'];?><br/>
									<input	type="radio"
											name="<?php echo 'M'.$row['hid'];?>"
											value="<?php echo $row['aid'];?>"
											 <?php if ($row['pid'] == $row['aid']) echo 'checked';?>/>
										<?php echo $row['aid'];?></td>
<?php
			if ($rounddata['ou_round'] == 't') {
?>								<td class="ou"><?php echo ($row['cs']+0.5);?></td>
								<td>
									<input	type="radio"
											name="<?php echo 'O'.$row['hid'];?>"
											value="U"
											<?php if ($row['over'] == 'f') echo 'checked';?>/>Under<br/>
									<input	type="radio"
											name="<?php echo 'O'.$row['hid'];?>" value="O"
											<?php if ($row['over'] == 't') echo 'checked';?>/>Over<br/></td>
<?php
			}
?>								<td><textarea name"<?php echo 'C'.$row['hid'];?>" class="mcomment" rows="2" cols="20"><?php echo $row['comment'];?></textarea></td>
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
		$result=dbQuery('SELECT * FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).';');
		$noopts = dbNumRows($result);  //No of multichoice examples 0= numeric answer required
		$resultop = dbQuery('SELECT * FROM option_pick WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND uid = '.dbMakeSafe($uid).';');
		if (dbNumRows($resultop) > 0 ) {
			$opdata = dbFetchRow($resultop);
		}
		if ($noopts ==0 ) {
			$row = dbFetchRow($result);
//Question is a numeric type
?>							<tr>
								<td><?php echo dbBBcode($rounddata['question']);?></td>
								<td><input id="answer" type="text" name="answer"
<?php
			if(!is_null($row['value'])) {
				echo 'value="'.$row['value'].'"';
			}
?>													/></td>
<?php
		} else {
//Question is multichoice
			for($i=1; $i<$noopts;$i++) {
				$row = dbFetchRow($result);
?>							<tr>
<?php
				if($i == 1) {
?>								<td class="bquestion" rowspan ="<?php echo $noopts ;?>"><?php echo dbBBcode($rounddata['question']);?></td>
<?php
				}
?>								<td><input type="radio" name="Canswer" value="<?php echo $row['opid'];?>"
										<?php if(isset($opdata) && $optdata['opid'] == $row['opid']) echo 'checked';?>	/><?php echo $row['label']; ?></td>
<?php
				
			}
		}
?>								<td>
<textarea class="bcomment" name="Cbonus"><?php if (isset($optdata) && !is_null($opdata['comment'])) echo $opdata['comment'];?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
<?php
	} // end valid question check
	dbFree($result);
	dbFree($resultop);
?>				</td>
			</tr>
<?php
	if($playoff_deadline != 0 and $playoff_deadline > $time_at_top) {
		require_once('team.php');
//Playoff selection is part of this competition
?>			<tr>
				<td id="playoff_pick" colspan="2">
<?php
$result=dbQuery('SELECT tid FROM div_winner_pick WHERE cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($uid).';');
		$dw = array();
		while($row = dbFetchRow($result)) {
			$dw[$row['tid']] = 1;
		}
dbFree($result);
$result=dbQuery('SELECT tid FROM wildcard_pick WHERE cid = '.dbMakeSafe($cid).' AND uid = '.dbMakeSafe($uid).';');
		$wild = array();
		while($row = dbFetchRow($result)) {
			$wild[$row['tid']] = 1;
		}
		$wild1_shown = false;
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
?>								<td class="tid"><?php echo $tid; ?></td>
								<td class="radio">
									<input type="radio" class="ppick" 
											name="<?php echo 'D'.$divid.$confid;?>"
											value="<?php echo $tid;?>"
											<?php if (isset($dw[$tid])) echo 'checked';?> /></td>
								<td class="radio">
									<input type="radio" class="ppick" 
											name="<?php echo 'W1'.$confid;?>"
											value="<?php echo $tid;?>"
											<?php if (isset($wild[$tid])) {echo 'checked'; $wild1_shown=true;} ?> /></td>
								<td class="radio">
									<input type="radio" class="ppick" 
											name="<?php echo 'W2'.$confid;?>"
											value="<?php echo $tid;?>"
											<?php if ($wild1_shown && isset($wild[$tid])) echo 'checked';?> /></td>
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
			<tr>
<?php
	}  //playoffs
?>
		</tbody>
	</table>
	<input id="make_picks" type="submit" value="Make Picks" />
</form>
<?php
}