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

$sql = "SELECT cid,description,uid,name FROM competition c LEFT JOIN participant p ON c.administrator = p.uid";
if(!isset($_GET['global'])) {
	// When not global administrator, only see competitions for which are administrator
	$sql .= " WHERE c.administrator = ? ORDER BY cid DESC";
	$c = $db->prepare($sql);
	$c->bindInt(1,$uid);
} else {
	$sql .= " ORDER BY cid DESC";
	$c = $db->prepare($sql);
}
?>	<form id="default_competition" action="setdefault.php">
		<table>
			<caption>Football Competitions</caption>
			<thead>
				<tr>
					<th class="ctitle">Title</th>
					<th class="user">Competition Administrator</th>
					<th class="radio">Default</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
<?php
$db->exec("BEGIN TRANSACTION");  //The whole page will run within one transaction - so its faster
$s = $db->prepare("SELECT value FROM settings WHERE name = ?");
$dcid = $s->fetchSetting("default_competition");
unset($s);

while($row = $c->FetchRow()) {
?>
				<tr>
					<td id="<?php echo 'C'.$row['cid'];?>" class="selectthis"><?php echo $row['description']; ?></td>
					<td id="<?php echo 'A'.$row['cid'];?>" class="selectthis"><?php echo $row['name'];?></td>
					<td>
						<input class="default" type="radio" name="defcomps" value="<?php echo $row['cid'];?>" 
							<?php if($dcid == $row['cid']) echo 'checked="checked"' ;?> />
					</td>
					<td><div id="<?php echo 'D'.$row['cid']; ?>" class="del"></div></td>
				</tr>
<?php
}
unset($c);
?>			</tbody>
		</table>
	</form>
<hr/>
<p id="compserr"></p>
	<form id="createform" action="createcomp.php">
		<table class="form">
			<caption>Create Competition</caption>
			<tbody>
				<tr>
					<td>
			<label>Competition Title<br/>
			<input id="desc" name="desc" type="text" class="ctitle" value="" /></label>
					</td>
					<td>
			<label>Administrator<br/>
			<select id="adm" name="adm" class="user">
<?php
//Participants who have logged in in the last year
$sql = "SELECT uid,name FROM participant WHERE last_logon > strftime('%s','now') - 31536000";
$sql .= " AND is_guest = 0 ORDER BY admin_experience DESC, name COLLATE NOCASE";
$u = $db->prepare($sql);
while ($row = $u->fetchRow()){
?>				<option value="<?php echo $row['uid'];?>"
                    <?php if ($row['uid'] == $uid) echo 'selected="selected"' ;?>><?php echo $row['name'] ;?></option>
<?php
}
unset($u);
$db->exec("ROLLBACK");
?>			</select></label>
					</td>
					<td class="option1">
			<label><input id="def" type="checkbox" name="setdefault" value="set" />Set as default</label>
					</td>
					<td class="submit">
						<input type="submit" value="Create" />
					</td>
				</tr>
			</tbody>
		</table>
	</form>
