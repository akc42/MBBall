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
require_once('./inc/team.inc');

?><h1>Players Playoff Picks</h1>
<?php

$sql = " SELECT u.uid, u.name, p.tid AS tid, COALESCE(s.score,0) AS score,s.confid AS confid,p.admin_made AS admin_made,p.submit_time AS submit_time";
$sql .= " FROM";
$sql .= "	registration r";
$sql .= " 	JOIN participant u USING (uid)";
$sql .= " 	JOIN playoff_score s USING(cid,uid)";
$sql .= " 	JOIN playoff_picks p USING (cid,uid,confid)";
$sql .= " WHERE";
$sql .= " 	r.cid = ? ";
$sql .= " ORDER BY";
$sql .= " 	s.confid,";
$sql .= " 	COALESCE(s.score,0) DESC,";
$sql .= " 	u.name COLLATE NOCASE";

$u = $db->prepare($sql);
$u->bindInt(1,$cid);

$lastconfid = "";
$lastuid = 0;

if($newrow = $u->fetchRow()) {
	$alldone = false;
} else {
	$alldone = true;
}

while(!$alldone){
	$row = $newrow;
	if(!($newrow = $u->fetchRow())) {
		$alldone = true;
	}
	if($lastconfid != $row['confid']) {
		$confid = $row['confid'];
		$conference = $confs[$confid];
		$lastconfid = $confid;
?><table>
	<thead>
		<tr><th rowspan="2"><?php echo $conference;?></th>
<?php
		foreach($divs as $divid => $division){
?>			<th colspan="<?php echo $sizes[$confid][$divid];?>">
				<?php echo $division; ?></th>
<?php
		}

?>			<th rowspan="2" class="score">Score</td>
		</tr>
		<tr>
<?php
	
		foreach($divs as $divid => $division) {
			if(isset($teams[$confid][$divid])) {
				foreach($teams[$confid][$divid] as $team) {
?>			<th class="tid"><div style="position:relative;><?php if($team['mp']) {echo tick();}echo $team['tid']; if($team['points'] <> 1) echo '<div class="xtra">'.($team['points']).'</div>';?></div></th>
<?php
				}
			} else {
?><th class="tid">&nbsp;</th>
<?php
	        }
		}
?>		</tr>
	</thead>
	<tbody>
<?php
		$lastuid = 0;
	}
	if($lastuid != $row['uid'])	{
		//Open a new row
?>		<tr>
			<td class="user_name"><?php echo $row['name']; ?></td>
<?php
		$playoff_selections = Array();
		$lastuid = $row['uid'];		
	}
	$playoff_selections[trim($row['tid'])] = ($row['submit_time'] > $playoff_deadline && $row['admin_made'] == 1)?'<span class="late_pick">L</span>':'';
		
	if( $alldone ||$newrow['uid'] != $lastuid) {
	//now we have to close the previous role
		foreach($divs as $divid => $division) {
    		if(isset($teams[$confid][$divid])) {
			    foreach($teams[$confid][$divid] as $team) {
				    $correct = ($team['mp'] && isset($playoff_selections[$team['tid']]));
    ?>				<td <?php if($correct) {echo 'class="tid win"';}else{ echo 'class="tid"';}?>><div style="position:relative;">
					    <?php 
			      if(isset($playoff_selections[$team['tid']])) echo $playoff_selections[$team['tid']].'<img src="images/sel.gif" alt="'.$team['tid'].' selected"/>';
			      if($correct) { 
				tick(); 
				if ($team['points'] <> 1) echo '<div class="xtra">'.$team['points'].'</div>';
			      }
				?></div></td>
	    <?php
			    }
    		} else {
?><td class="tid">&nbsp;</td>
<?php
    		}
        }
?>			<td class="score"><?php echo $row['score'];?></td>
		</tr>
<?php
	}
	if($alldone || $newrow['confid'] != $lastconfid) {
?>	</tbody>
</table>
<?php
	}
}
unset($newrow);
unset($playoff_selections);
unset($u);
?>
