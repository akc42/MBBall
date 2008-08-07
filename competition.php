<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
$cid = $_GET['cid'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$resultcomp=dbQuery('SELECT * FROM competition WHERE cid = '.dbMakeSafe($cid).';');
if($comp = dbFetchRow($resultcomp)) {
?>
<form id="compform" action="updatecomp.php" >
	<input type="hidden" name="uid" value="<?php echo $uid;?>" />
	<input type="hidden" name="pass" value="<?php echo $pass;?>" />
	<input type="hidden" name="cid" value="<?php echo $cid;?>" />
	<!-- condition is first so it can be floated right -->
	<table class="form">
		<caption>Competition Details</caption>
		<tbody>
			<tr>
				<td colspan="2">
		<label>Competition Title<br/>
		<input id="description" name="description" type="text" class="ctitle" value="<?php echo $comp['description'];?>" /></label>
				</td>
				<td>
<?php
	if(isset($_GET['global'])) {	
?>	
		<label>Administrator<br/>
		<select id="administrator" name="administrator" class="user">
<?php
		$resultusers = dbQuery('SELECT uid,name FROM participant WHERE last_logon > now() - interval \'1 year 1 month\' AND
						 is_bb IS NOT TRUE ORDER BY admin_experience DESC, name;');
		$userdata = dbFetch($resultusers);
		dbFree($resultusers);
		foreach($userdata as $user) {
?>			<option value="<?php echo $user['uid'];?>" 
				<?php if ($user['uid'] == $comp['administrator']) echo 'selected="selected"' ;?>>
					<?php echo $user['name'] ;?></option>
<?php
		}
?>		</select></label>
<?php
} else {
?>	<input type="hidden" name="adm" value="<?php echo $user['uid'];?>" />
	<?php echo $comp['name'];?>
<?php
}
?>				</td>
				<td class="comment" rowspan="3">
		<label>Condition for joining competition<br/>
		<textarea id="condition" ><?php echo $comp['condition'];?></textarea></label>
				</td>
			</tr>
			<tr>
				<td class="option2">	
		<label><input id="open" name="open" type="checkbox" value="set" 
			<?php if ($comp['open'] == 't') echo 'checked="checked"' ;?>/>Can Register</label>
				</td>
				<td class="option1" colspan="2">
		<label><input id="bbapproval" name="bbaproval" type="checkbox" value="set"
			<?php if ($comp['bb_approval'] == 't') echo 'checked="checked"' ;?>/>BB's need Approval</label>
				</td>
			</tr>
			<tr>
				<td colspan="3">
		<label>Playoff Selection Deadline<br/>(leave blank for no playoff selection)<br/>
		<input id ="playoffdeadline" name="playoffdeadline" class="time" value="<?php echo $comp['pp_deadline'];?>" />
				</td>
			</tr>
			<tr><td colspan="3"></td>
				<td class="submit">
					<input type="submit" value="Update" />
				</td>
			</tr>
		</tbody>
	</table>
</form>

<?php
}
dbFree($resultcomp);
