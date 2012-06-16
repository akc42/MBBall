<?php
/*
 	Copyright (c) 2008-2012 Alan Chandler
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
require_once('./inc/db.inc');
if(!(isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['auid']) && isset($_GET['gap']) && isset($_GET['pod']) && isset($_GET['name']) ))
	forbidden();

$cid = $_GET['cid'];
$rid = $_GET['rid'];
$gap = $_GET['gap'];
$playoff_deadline = $_GET['pod'];

if($rid != 0 && $cid !=0) {

	$time_at_top = time();
	$r =$db->prepare("SELECT * FROM round WHERE open = 1 AND cid = ? AND rid = ?");
	$r->bindInt(1,$cid);
	$r->bindInt(2,$rid);
	if($rounddata = $r->fetchRow()) {
		$m = $db->prepare("SELECT COUNT(*) FROM match WHERE cid = ? AND rid = ? AND open = 1");
		$m->bindInt(1,$cid);
		$m->bindInt(2,$rid);
		$nomatches = $m->fetchValue();
		unset($m);
	// If user is registered and we can do picks then we need to display the  Picks Section
		$sql = "SELECT m.hid , m.aid , p.pid , m.combined_score AS cs, p.over_selected , p.comment AS comment, m.comment AS adm_comment, m.match_time";
		$sql .= " FROM match m LEFT JOIN pick p ON m.cid = p.cid AND m.rid = p.rid AND m.hid = p.hid AND p.uid = ?";
		$sql .= " WHERE m.cid = ? AND m.rid = ? AND m.open = 1 AND m.match_time > ?";
		$sql .= ' ORDER BY m.match_time, m.hid;';
		$m = $db->prepare("SELECT * FROM match WHERE cid = ? AND rid = ? AND open = 1 ORDER BY match_time, hid LIMIT 8 OFFSET ?");
		$m->bindInt(1,$uid);
		$m->bindInt(2,$cid);
		$m->bindInt(3,$rid);
		$m->bindInt(4,($time_at_top +$gap));
	}
	unset($r);
	
	if(($playoff_deadline != 0 && $playoff_deadline > $time_at_top) || $rounddata && ($nomatches > 0 || ( $rounddata['valid_question'] == 1  && $rounddata['deadline'] > $time_at_top))) {
?><form id="pick">
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
	<input type="hidden" name="rid" value="<?php echo $rid;?>" />
	<input type="hidden" name="gap" value="<?php echo $gap;?>" />
	<input type="hidden" name="ppd" value="<?php echo $playoff_deadline ;?>" />
	<input type="hidden" name="bqdeadline" value="<?php echo $rounddata['deadline'];?>" />	<table class="layout">
		<caption>Entering Picks on behalf of :<?php echo $_GET['name'];?></caption>
		<tbody>
<?php	
		require_once('./inc/bbcode.inc');
		if ($rounddata && ($nomatches > 0 || $rounddata['valid_question'] == 1 )) {
?>			<tr>
				<td id="picks">
<?php
			if($nomatches >0) {
?>					<table>
						<caption>Match Picks for <?php echo $rounddata['name'];?></caption>
						<thead>
							<tr>
								<th class="team">Team Pick</th>
<?php
				if ($rounddata['ou_round'] == 1) {
?>								<th class="score">Combined Score</th>
<?php
				}
?>								<th class="comment">Administrators Comment</th>
								<th class="comment">Players Comment</th>
							</tr>
						</thead>
						<tbody>
<?php
				while($row=$m->FetchRow()) {
					$row['hid']=trim($row['hid']);
					$row['aid']=trim($row['aid']);
					$row['pid']=trim($row['pid']);
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
													<?php if ($row['pid'] == $row['hid']) echo 'checked="checked"';?>/></td>
												<td><input	type="radio"
													name="<?php echo 'M'.$row['hid'];?>"
													value="<?php echo $row['aid'];?>"
											 		<?php if ($row['pid'] == $row['aid']) echo 'checked="checked"';?>/></td>
											</tr>
										</tbody>
									</table>
								</td>
<?php
					if ($rounddata['ou_round'] == 1) {
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
			unset($m);
?>				</td>
				<td id="bonus_pick">
<?php	
			if ($rounddata['valid_question'] == 1) {
?>					<table>
						<caption>Bonus Question</caption>
						<thead>
							<tr><th class="bq">Question</th><th class="ba">Answer</th><th class="comment">Comment</th></tr>
						</thead>
						<tbody>
<?php
				$o = $db->prepare("SELECT COUNT(*) FROM option WHERE cid = ? AND rid = ?");
				$o->bindInt(1,$cid);
				$o->bindInt(2,$rid);
				$noopts = $o->fetchValue();
				unset($o);
				$o = $db->prepare("SELECT * FROM option WHERE cid = ? AND rid = ? ORDER BY opid");
				$o->bindInt(1,$cid);
				$o->bindInt(2,$rid);

				$p = $db->prepare("SELECT * FROM option_pick WHERE cid = ? AND rid = ? AND uid = ? ORDER BY opid");
				$optdata = $p->FetchRow(); //even on multichoice there is only going to be one pick
				unset($p);
				
				if ($noopts > 0) {
		//Question is multichoice
					$firsttodo = true;
					while($row = $o->fetchRow()) {
?>							<tr>
<?php
						if($firsttodo) {
?>								<td class="question" rowspan ="<?php echo $noopts ;?>"><?php echo dbBBcode($rounddata['question']);?></td>
								
<?php
						}

?>								<td><input type="radio" name="opid" value="<?php echo $row['opid'];?>" <?php if((int)$optdata['opid'] == (int)$row['opid']) echo 'checked="checked"';?>	/><?php echo $row['label'];?></td>
<?php

						if($firsttodo) {
?>								<td rowspan ="<?php echo $noopts ;?>">
<textarea class="comment" name="Cbonus"><?php echo $optdata['comment'];?></textarea>
								</td>
<?php
						}
						$firsttodo = false;
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
				unset($o);
				unset($optdata);
?>						</tbody>
					</table>
<?php
			} // end valid question check
?>				</td>
			</tr>
<?php
		}
		if($playoff_deadline != 0 && $playoff_deadline > $time_at_top) {
			require_once('./inc/team.inc');
		//Playoff selection is part of this competition
?>			<tr>
				<td id="playoff_pick" colspan="2">
<?php
			$w = $db->prepare("SELECT tid,divid,confid FROM div_winner_pick WHERE cid = ? AND uid = ? ");
			$w->bindInt(1,$cid);
			$w->bindInt(2,$uid);
			$dw = array(array());
			while($row = $w->FetchRow()) {
				$row['tid'] = trim($row['tid']);			
				$dw[$row['confid']][$row['divid']] = $row['tid'];
			}
			unset($w);
			$w = $db->prepare("SELECT tid,opid,confid FROM wildcard_pick WHERE cid = ? AND uid = ? ");
			$w->bindInt(1,$cid);
			$w->bindInt(2,$uid);
			$wild = array(array());
			while($row = $w->FetchRow()) {
				$row['tid'] = trim($row['tid']);
				$wild[$row['confid']][$row['opid']] = $row['tid'];
			}
			unset($w);
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
	unset($rounddata);
}
?>
