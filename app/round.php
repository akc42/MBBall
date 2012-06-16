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
if(!(isset($_GET['cid']) && isset($_GET['rid']) )) forbidden();

$cid = $_GET['cid'];
$rid = $_GET['rid'];
if($rid != 0 && $cid !=0) {
	$r = $db->prepare("SELECT * FROM round WHERE cid = ? AND rid = ? ");
	$r->bindInt(1,$cid);
	$r->bindInt(2,$rid);
	$row = $r->fetchRow();
	unset($r);
	
	$o = $db->prepare("SELECT COUNT(*) FROM option WHERE cid = ? AND rid = ?");
	$o->bindInt(1,$cid);
	$o->bindInt(2,$rid);
	$opts = $o->fetchValue();
	unset($o);
	
?>
<form id="roundform" action="updateround.php" >
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
	<input type="hidden" name="rid" value="<?php echo $rid;?>" />
	<table class="form">
		<caption>Round Details</caption>
		<tbody>
			<tr>
				<td colspan="2">
		<label>Round Name<br/>
		<input id="rname" type="text" name="rname" class="rname" value="<?php echo $row['name'];?>"/></label>
				</td>
				<td rowspan="4" colspan="2">
		<label>Question<br/>
			<textarea id="question" name="question"><?php echo $row['question'];?></textarea>
		</label>
				</td>
			</tr>
			<tr>
				<td class="option1">
		<label><input id="ou" name="ou" type="checkbox" <?php if($row['ou_round'] == 1 ) echo 'checked="checked"';?> />Use Over Under Selection</label>
				</td>
			</tr>
			<tr>
				<td>
		<label>Points for correct pick<br/>
			<input id="value" name="value" type="text" value="<?php echo $row['value'];?>" />
		</label>
				</td>
			</tr>
			<tr>
				<td>
		<label><input id="roundopen" name="open" type="checkbox" 
			<?php if ($row['open'] == 1) echo 'checked="checked"';?> />Round Open</label>
				</td>
				<td>
		<label><input id="validquestion" name="validquestion" type="checkbox" 
			<?php if ($row['valid_question'] == 1) echo 'checked="checked"';?> />Valid Question?</label>
				</td>
			</tr>
			<tr>
				<td colspan="2">
		<label>Deadline for answering question<br/>
			<input id="deadline" name="deadline" type="hidden" value="<?php echo $row['deadline'];;?>"/>
		</label>
				</td>
				<td>
		<label>Answer<br/>
			<input id="answer" name="answer" value="<?php echo $row['answer'];?>" 
				<?php if($opts > 0) echo 'disabled="disabled"';?> />
		</label>
				</td>
                <td id="option"><p>Add<br/>MultiChoice<br/>Option</p></td>
			</tr>
		</tbody>
	</table>
</form>
<?php
} else {
?><p>There is no Round information to display right now</p>
<?php
}
?>
