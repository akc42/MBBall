<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['rid']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];
if($cid !=0) {

	$rid = $_GET['rid'];
	
	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');
?>
<form id="createroundform" action="createround.php">
	<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
	<input type="hidden" name="pass" value="<?php echo $password; ?>" />
	<input type="hidden" name="cid" value="<?php echo $cid; ?>" />
	<input id="nextrid" type="hidden" name="rid" value="<?php echo $rid; ?>" />
	<table class="form">
		<caption>Create Round</caption>
		<tbody>
			<tr>
				<td><?php echo $rid; ?></td>
				<td><input id="rname2" class="rname" type="text" name="rname" value="Round <?php echo $rid;?>" /></td>
				<td class="option2"><label><input id="ou2" type="checkbox" name="ou" />Over/Under</label></td>
				<td class="submit"><input type="submit" value="Create" /></td>
			</tr>
		</tbody>
	</table>
</form>
<?php
} else {
?><p>Cannot create New Round right now</p>
<?php
}

