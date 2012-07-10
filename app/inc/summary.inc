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
$usedCache = false;
if($rid == MBBALL_MAX_RID) {
  if(!is_null($competitionCache) && ( MBBALL_CACHE_AGE == 0 || (($SERVER['REQUEST_TIME'] - $competitionCacheDate) < MBBALL_CACHE_AGE))) {
    //We have a valid cache so use it
    $summary = unserialize($competitionCache);
    $usedCache = true;
  }
  unset($c);
}
unset($competitionCache);
if(!isset($summary)) {
  //We don't have the cache data, so we need to do the query
  if ($rid != 0) {
    $sql = "SELECT r.rid AS rid, r.name AS rname,r.score AS score,t.uid AS uid,t.name AS name,t.rscore AS rscore,t.pscore AS pscore,(t.rscore + t.pscore) AS tscore";
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
    $sql .=  "	  SELECT cid,uid,rounds.name, rounds.rid,score"; 
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
    $sql = "SELECT 0 As score,u.uid AS uid, u.name AS name, 0 AS pscore, 0 AS rscore, 0 AS tscore";
    $sql .= " FROM participant u JOIN registration r USING (uid) WHERE cid = ? ORDER BY u.name COLLATE NOCASE";
    $r = $db->prepare($sql);
    $r->bindInt(1,$cid);
  }
  $lastuid = 0;
  $first_user = true;
  $summary['round'] = Array();
  $summary['user'] = Array();
  while ($row = $r->fetchRow()) {
    if($row['uid'] <> $lastuid) {
      //This is the first time with new user
      if ($lastuid <> 0) {
	//save previous user
	$summary['user'][] = $user;
	$first_user = false;
	unset($round);
      }
      $user = Array();
      $user['uid'] = $row['uid'];
      $user['name'] = $row['name'];
      $user['pscore'] = $row['pscore'];
      $user['rscore'] = $row['rscore'];
      $user['tscore'] = $row['tscore'];
      $user['scores'] = Array();
      $lastuid = $row['uid'];
    }
    $user['scores'][] = $row['score'];
    if($first_user && $rid <> 0) {
      $round = Array();
      $round['rid'] = $row['rid'];
      $round['name'] = $row['rname'];
      $summary['round'][] = $round;
    }
  }
  if(isset($user)) $summary['user'][] = $user; //need to save the last user (provided we have had at least one user)
  unset($user);
  unset($r);
  if($rid == MBBALL_MAX_RID) {
    //We can store the result in the cache.
    $c = $db->prepare("UPDATE competition SET results_cache = ?, cache_store_date = (strftime('%s','now')) WHERE cid = ?");
    $c->bindString(1,serialize($summary));
    $c->bindInt(2,$cid);
    $c->exec();
    unset($c);
  }
}

?><table>
	<caption>Overall Results<?php if(!$usedCache) echo '<span class="without_cache">&nbsp;</span>';?></caption>
	<thead>
		<tr><th class="user_name">Name</th>
<?php

foreach($summary['round'] as $round)  {
?>			<th><?php echo $round['name']; ?></th>
<?php
}

?>			<th class="score">Rounds<br/>Total</th>
			<th class="score">PlayOffs<br/>Total</th>
			<th class="score">Grand<br/>Total</th>
		</tr>
	</thead>
	<tbody>
<?php

foreach($summary['user'] as $user){
?>		<tr>
			<td <?php if($uid == $user['uid']) {echo 'class="user_name me"';} else {echo 'class="user_name"';}?>><?php echo $user['name'];?></td>
<?php
  foreach($user['scores'] as $score) {
?>			<td <?php if($uid == $user['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $score;?></td>
<?php
  }
?>			<td <?php if($uid == $user['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $user['rscore'];?></td>
			<td <?php if($uid == $user['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $user['pscore'];?></td>
			<td <?php if($uid == $user['uid']) {echo 'class="score me"';} else {echo 'class="score"';}?>><?php echo $user['tscore'];?></td>
		</tr>
<?php

}
unset($summary);
?>	</tbody>
</table>
