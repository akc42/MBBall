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
?><table>
	<caption>Overall Results</caption>
	<thead>
		<tr><th class="user_name">Name</th>
<?php
$noRounds = 0;
$r = $db->prepare("SELECT name FROM round WHERE open = 1 AND cid = ? and rid <= ? ORDER BY rid DESC LIMIT ".MBBALL_MAX_ROUND_DISPLAY);
$r->bindInt(1,$cid);
$r->bindInt(2,$rid);
while($row = $r->fetchRow()) {
?>			<th><?php echo $row['name']; ?></th>
<?php
	$noRounds++; //Keep a count of rounds so we can know when to add the summary
}
unset($r);
?>			<th class="score">Rounds<br/>Total</th>
			<th class="score">PlayOffs<br/>Total</th>
			<th class="score">Grand<br/>Total</th>
		</tr>
	</thead>
	<tbody>
<?php

if ($rid != 0) {
	$sql = "SELECT r.score AS score,t.uid AS uid,t.name AS name,t.rscore AS rscore,t.pscore AS pscore,(t.rscore + t.pscore) AS tscore";
	$sql .=  " FROM (";
	$sql .=  "  SELECT r.cid,u.uid,u.name AS name,sum(rs.score) AS rscore,p.pscore";
	$sql .=  "  FROM participant u JOIN registration r USING (uid)";
	$sql .=  "   JOIN round_score rs USING (cid,uid)";
	$sql .=  "   JOIN (";
	$sql .=  "	   SELECT cid,uid,sum(score) as pscore";
	$sql .=  "     FROM playoff_score GROUP BY cid,uid";
	$sql .=  "	 ) p USING (cid,uid)";
	$sql .=  "  GROUP BY r.cid,u.uid,u.name,p.pscore";
	$sql .=  " ) t";
	$sql .=  "  JOIN (";
	$sql .=  "	  SELECT cid,uid, rounds.rid,score"; 
	$sql .=  "    FROM round_score rs JOIN (";
	$sql .=  "      SELECT cid,rid,name FROM round";
	$sql .=  "      WHERE cid = ? AND open = 1 AND rid <= ?";
	$sql .=  "      ORDER BY rid DESC LIMIT ".MBBALL_MAX_ROUND_DISPLAY;
	$sql .=  "  ) AS rounds USING (cid,rid)"; 
	$sql .=  " ) r USING (cid,uid)";  
	$sql .=  " ORDER BY (pscore + rscore) DESC, t.name COLLATE NOCASE,rid DESC";
	$r = $db->prepare($sql);
	$r->bindInt(1,$cid);
	$r->bindInt(2,$rid);
} else {
	$sql = "SELECT 0 As score,u.uid AS uid, u.name AS name, 0 AS pscore, 0 AS rscore, 0 AS total";
	$sql .= " FROM participant u JOIN registration r USING (uid) WHERE cid = ? ORDER BY u.name COLLATE NOCASE";
	$r = $db->prepare($sql);
	$r->bindInt(1,$cid);
}
$lastuid = 0;
while ($row = $r->fetchRow()) {
	if($row['uid'] <> $lastuid) {
		$lastuid = $row['uid'];
		$rowcounter = $noRounds;
?>		<tr>
			<td <?php if($uid == $row['uid']) {echo 'class="user_name me"';} else {echo 'class="user_name"';}?>><?php echo $row['name'];?></td>
<?php
	}
	if($rid != 0) {
?>			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $row['score'];?></td>
<?php
	}
	if(--$rowcounter <= 0) {
?>			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $row['rscore'];?></td>
			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $row['pscore'];?></td>
			<td <?php if($uid == $row['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $row['tscore'];?></td>
		</tr>
<?php
	}
}
unset($r);
?>	</tbody>
</table>
