<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['bbar']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];


if ($cid != 0) {
	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');
	$result = dbQuery('SELECT * FROM registration r JOIN participant u USING (uid) WHERE r.cid = '.dbMakeSafe($cid).' ORDER BY agree_time ;');

?>
<table>
	<caption>Registered Users</caption>
	<thead>
		<tr>
			<th>Name</th>
			<th>E-Mail</th>
			<th>Last Logon</th>
			<th>When Registered</th>
			<th>Is a BB</th>
			<th>BB Approved</th>
			<th>As been a Admin</th>
		</tr>
	</thead>
	<tbody>
<?php
	while($row = dbFetchRow($result)) {
?>		<tr>
			<td><?php echo $row['name'];?></td>
			<td><?php echo $row['email'];?></td>
			<td><span class="time"><?php echo $row['last_logon']; ?></span></td>
			<td><span class="time"><?php echo $row['agree_time']; ?></span></td>
			<td><input type="checkbox" disabled="disabled" <?php if($row['is_bb'] == 't') echo 'checked="checked"';?> /></td>
			<td>
				<input type="checkbox" name="<?php echo $row['uid'];?>"
<?php 
		if($row['is_bb'] == 't') {
?>					class="bbapprove"
<?php
			 if($_GET['bbar'] == 'false') {
?>					disabled="disabled"
<?php
			}
		} else {
?>					disabled="disabled"
<?php
		}		 
		if($row['bb_approved'] == 't') {
?>					checked="checked"
<?php
		}
?>								 />
			</td>
			<td><input type="checkbox" disabled="disabled" <?php if($row['admin_experience'] == 't') echo 'checked="checked"';?> /></td>
		</tr>
<?php
	}
	dbFree($result);

?>	</tbody>
</table>
<?php
} else {
?><p>No Registered User data is available right now</p>
<?php
}
