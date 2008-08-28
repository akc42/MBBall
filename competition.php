<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
$cid = $_GET['cid'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
if ($cid != 0) {
	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');
	$resultcomp=dbQuery('SELECT * FROM competition WHERE cid = '.dbMakeSafe($cid).';');
	if($comp = dbFetchRow($resultcomp)) {
?>
<form id="compform" action="updatecomp.php" >
	<input type="hidden" name="uid" value="<?php echo $uid;?>" />
	<input type="hidden" name="pass" value="<?php echo $password;?>" />
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
			$resultusers = dbQuery('SELECT uid,name FROM participant WHERE last_logon > extract(epoch from now()) - 31536000 AND
							 is_bb IS NOT TRUE ORDER BY admin_experience DESC, name;');
			$userdata = dbFetch($resultusers);
			dbFree($resultusers);
			foreach($userdata as $user) {
?>			<option value="<?php echo $user['uid'];?>" 
				<?php if ($user['uid'] == $comp['administrator']) echo 'selected="selected"' ;?>><?php echo $user['name'] ;?></option>
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
				<td class="comment" rowspan="4">
		<label>Condition for joining competition<br/>
		<textarea id="condition" name="condition"><?php echo $comp['condition'];?></textarea></label>
				</td>
			</tr>
			<tr>
				<td class="option2">	
		<label><input id="open" name="open" type="checkbox" 
			<?php if ($comp['open'] == 't') echo 'checked' ;?>/>Can Register</label>
				</td>
				<td class="option1" colspan="2">
		<label><input id="bbapproval" name="bbapproval" type="checkbox"
			<?php if ($comp['bb_approval'] == 't') echo 'checked' ;?>/>BB's need Approval</label>
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
					<input id="gap" name="gap" value="<?php echo intval($comp['gap']/60);?>" /></label>
			</tr>
		</tbody>
	</table>
</form>

<?php
	}
	dbFree($resultcomp);
} else {
?><p>There is no Competition to display right now</p>
<?php
}
