<?php
if (!defined('BALL'))
	die('Hacking attempt...');

// If user is registered and we can do picks then we need to display the  Picks Section
$sql = 'SELECT m.hid AS hid, m.aid AS aid, p.pid AS pid, m.combined_score AS cs, p.over AS over p.comment AS comment';
$sql .= ' FROM match m JOIN team t ON m.hid = t.tid LEFT JOIN pick p ';
$sql .= 'ON m.cid = p.cid AND m.rid = p.rid AND m.hid = p.hid AND p.uid = '.dbMakeSafe($uid);
$sql .= ' WHERE m.cid = '.dbMakeSafe($cid).' AND m.rid = '.dbMakeSafe($rid).'WHERE m.open IS TRUE AND m.match_time > '.dbMakeSafe(time()+$gap);
$sql .= ' ORDER BY t.confid,t.divid, m.hid;';
$result = dbQuery($sql);
$nomatches = dbNumRows($result);
$time_at_top = time();
		if ($nomatches > 0 || $rounddata['valid_question']||
			($playoff_deadline != 0 and $play_off_deadline > $time_at_top)) {
?><form id="pick">
<?php
		}
		if($nomatches >0) {
?>	<div id="picks">
		<table>
		<caption>Match Picks for <?php echo $rounddata['name'];?></caption>
		<thead>
			<tr>
				<th class="team">Team Pick</th>
<?php
			if ($rounddata['ou_round'] == 't') {
?>				<th class="score">Score</th><th class="ou_select">Over/Under</th>
<?php
			}
?>
				<th class="comment">Comment</td>
			</tr>
		</thead>
		<tbody>
<?php
			while($row=dbFetchRow($result)) {
?>			<tr>
				<td>
					<input class="pick" type="radio" 
						name="<?php echo $row['hid'];?>"
						value="<?php echo $row['hid'];?>"
						 <?php if ($row['pid'] == $row['hid']) echo 'checked';?>/><?php echo $row['hid'];?><br/>
					<input class="pick" type="radio" 
						name="<?php echo $row['hid'];?>"
						value="<?php echo $row['aid'];?>"
						 <?php if ($row['pid'] == $row['aid']) echo 'checked';?>/><?php echo $row['aid'];?></td>
<?php
				if ($rounddata['ou_round'] == 't') {
?>				<td class="ou"><?php echo ($row['cs']+0.5);?></td><td>
					<input class="pick" type="radio"
						name="<?php echo $row['aid'];?>" value="U" 
						<?php if ($row['over'] == 'f') echo 'checked';?>/>Under<br/>
					<input class="pick" type="radio"
						name="<?php echo $row['aid'];?>" value="O" 
						<?php if ($row['over'] == 't') echo 'checked';?>/>Over<br/></td>
<?php
	}
?>				<td><textarea class="pick" rows="2" cols="20"><?php echo $row['comment'];?></textarea></td>
			</tr>
<?php
				}
			}
		
?>		</tbody>
		</table>
<?php
		}
		dbFree($result);
		if ($rounddata['valid_question']) {
?>		<table>
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
?>			<tr><td><?php echo $rounddata['question'];?></td><td><input type="text" name="answer"
<?php
				if(!is_null($row['value'])) {
					echo 'value="'.$row['value'].'"';
				}
?>													/></td>
			<td><textarea id=bonus_comment">
<?php
				if (isset($optdata) && !is_null($opdata['comment'])) echo $opdata['comment'];
?>			</textarea></td></tr>
<?php 
			} else {
//Question is multichoice
				for($i=1; $i<$noopts;$i++) {
					$row = dbFetchRow($result);
?>			<tr>
<?php
					if($i == 1) {
?>				<td rowspan ="<?php echo $noopts ;?>"><?php echo $rounddata['question'];?></td>
<?php
					}
?>				<td><input type="radio" name="answer" value="<?php echo $row['oid'];?>"
<?php
					if(isset($opdata) && $optdata['oid'] == $row['oid']) echo 'checked';
?>					/> <?php echo $row['label']; ?></td>
<?php
					if($i == 1) {
?>				<tdrowspan ="<?php echo $noopts ;?>"><textarea id=bonus_comment">
<?php
						if (isset($optdata) && !is_null($opdata['comment'])) echo $opdata['comment'];
?>					</textarea></td>
<>php
					}
?>			</tr>
<?php
				}
			}
?>		</tbody>
		</table>
<?php
		dbFree($result);
		dbFree($resultop);
?>	</div>
<?php
		}
	} //end of check for rounddata
	if($playoff_deadline != 0 and $play_off_deadline > $time_at_top) {
//Playoff selection is part of this competition
?>	<div id="playoff">
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
?>		<table>
		<caption>Pick divisional winner and wildcard picks for each conference</caption>
		<thead>
			<tr>
				<th class="po_h1">\</th><th class="po_h2">Division</th>
<?php
		foreach($divs as $division) {
			// for each division we are building a team id, name and logo columns
?>				<th class="t_dn" colspan="4"><?php echo $division;?></th>
<?php
		}
?>			</tr>		
			<tr>
				<th class="po_h3">Conference</th><th class="po_h4">\</th>
<?php
		foreach($divs as $division) {
?>				<th class="po_h5">Team</th>
				<th class="po_h6">D Win</th>
				<th class="po_h6">Wild1</th>
				<th class="po_h6">Wild2</th>
<?php
		}
?>			</tr>	
		</thead>
		<tbody>
<?php
		foreach($confs as $confid => $conference) {
			$no_of_rows = max($sizes[$confid]);
?>			<tr>
				<td class="po_b1" colspan="2" rowspan="<?php echo $no_of_rows;?>"><?php echo $conference;?></td>
<?php
			for ($i = 0; $i < $no_of_rows-1;$i++) {
				if( $i != 0) {
?>			<tr>
<?php
				}
				foreach($divs as $divid => $division) {
					if(isset($teams[$config][$divid][$i])) { //only if this entry is set
						$tid=$teams[$config][$divid][$i]['tid'];
?>				<td><?php echo $tid; ?></td>
				<td class="po_pdw">
					<input type="radio" 
						name="<?php echo $confid.$divid;?>"
						value="<?php echo $tid;?>"
						<?php if (isset($dw[$tid])) echo 'checked';?> />
				</td>
				<td class="po_wild">
					<input type="radio" 
						name="<?php echo $confid.'w1';?>"
						value="<?php echo $tid;?>"
						<?php if (isset($wild[$tid])) {echo 'checked'; $wild1_shown=true;} ?> />
				</td>
				<td class="po_wild">
					<input type="radio" 
						name="<?php echo $confid.'w2';?>"
						value="<?php echo $tid;?>"
						<?php if ($wild1_shown && isset($wild[$tid])) echo 'checked';?> />
			</td>
<?php
					}
				}
?>			</tr>
<?php
			}
		}
?>		</tbody>
		</table>
	</div>
<?php
	}  //playoffs
	if ($nomatches > 0 || $rounddata['valid_question']||
		($playoff_deadline != 0 and $play_off_deadline > $time_at_top)) {
?>	<input type="submit" name="pick_submit" value="Make Picks" />
</form>