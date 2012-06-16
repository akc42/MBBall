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
if (!isset($_GET['cid'])) forbidden();
$cid = $_GET['cid'];

if ($cid != 0) {

	
	$c = $db->prepare("SELECT c.*,u.name FROM competition c LEFT JOIN participant u ON u.uid = c.administrator WHERE cid = ?");
	$c->bindInt(1,$cid);	
	if($comp = $c->FetchRow()) {
?>
<form id="compform" action="updatecomp.php" >
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
	<!-- condition is first so it can be floated right -->
	<table class="form">
		<caption>Competition Details</caption>
		<tbody>
			<tr>
				<td colspan="2">
		<label>Competition Title<br/>
		<input id="description" name="desc" type="text" class="ctitle" value="<?php echo $comp['description'];?>" /></label>
				</td>
				<td>
<?php
		if(isset($_GET['global'])) {	
?>	
		<label>Administrator<br/>
		<select id="administrator" name="adm" class="user">
<?php
			$sql = "SELECT uid,name FROM participant WHERE last_logon > strftime('%s','now') - 31536000";
			$sql .= " AND is_guest = 0 ORDER BY admin_experience DESC, name COLLATE NOCASE";
			$u = $db->prepare($sql);
			while ($user = $u->fetchRow()){

?>			<option value="<?php echo $user['uid'];?>" 
				<?php if ($user['uid'] == $comp['administrator']) echo 'selected="selected"' ;?>><?php echo $user['name'] ;?></option>
<?php
			}
			unset($u);
			unset($user);
?>		</select></label>
<?php
		} else {
?>	<input type="hidden" name="adm" value="<?php echo is_null($comp['administrator'])?0:$comp['administrator'];?>" />
	<?php echo $comp['name'];
		}
?>				</td>
				<td class="comment" rowspan="4">
		<label>Condition for joining competition<br/>
		<textarea id="condition" name="condition"><?php echo $comp['condition'];?></textarea></label>
				</td>
			</tr>
			<tr>
				<td class="option2">	
		<label><input id="open" name="open" type="checkbox" 
			<?php if ($comp['open'] == 1) echo 'checked="checked"' ;?>/>Can Register</label>
				</td>
				<td class="option1" colspan="2">
		<label><input id="bbapproval" name="bbapproval" type="checkbox"
			<?php if ($comp['guest_approval'] == 1) echo 'checked="checked"' ;?>/>BB's need Approval</label>
				</td>
			</tr>
			<tr>
				<td colspan="3">
		<label>Playoff Selection Deadline<br/>(leave blank for no playoff selection)<br/>
		<input id ="playoffdeadline" type="hidden" name="playoffdeadline" value="<?php echo $comp['pp_deadline'];?>" /></label>
				</td>
			</tr>
			<tr>
				<td colspan="3"><label>Pick Deadline (minutes before match time)<br/>
					<input id="gap" name="gap" value="<?php echo intval($comp['gap']/60);?>" /></label></td>
			</tr>
		</tbody>
	</table>
</form>

<?php
	}
	unset($c);
} else {
?><p>There is no Competition to display right now</p>
<?php
}
?>
