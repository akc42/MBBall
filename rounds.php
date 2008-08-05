<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$result = dbQuery('SELECT rid,name,ou_round FROM round WHERE cid = '.dbMakeSafe($cid)';');
?><div id="rounds">
	<table>
		<caption>Rounds</caption>
		<thead>
			<tr>
				<th>No</th>
				<th>Round Name</th>
				<th>Over/Under</th>
				<th>DEL</th>
			</tr>
		</thead>
		<tbody>
<?php
$nextrid = dbNumRows($result) + 1;
while($row = dbFetchRow($result)) {
	$rid = $row['rid'];
?>			<tr>
				<td id="<?php echo 'R'.$rid;?>" class="rdlink"><?php echo $rid; ?></td>
				<td id="<?php echo 'N'.$rid;?>" class="rdlink"><?php echo $row['name'];?>
				<td><input type="checkbox" disabled="disabled" 
					<?php if($row['ou_round'] == 't') echo 'checked="checked"' ;?> /></td>
				<td><div id="<?php echo 'D'.$rid; ?>" class="del"></div></td>
			</tr>
<?php
}
dbFree($result);
?>		</tbody>
	</table>
<hr/>
	<form id="createroundform" action="createround.php">
		<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
		<input type="hidden" name="pass" value="<?php echo $password; ?>" />
		<input type="hidden" name="cid" value="<?php echo $cid; ?>" />
		<input type="hidden" name="rid" value="<?php echo $nextrid; ?>" />
		<table>
			<tbody>
				<tr>
					<td><?php echo $nextrid; ?></td>
					<td><input id="rname2" type="text" name="rname" value="Round <?php echo nextrid;?>" /></td>
					<td><input id="ou2" type="checkbox" name="ou" /></td>
					<td><input type="submit" value="Create" /></td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<div id="round"></div>