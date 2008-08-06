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
$result = dbQuery('SELECT rid,name,ou_round FROM round WHERE cid = '.dbMakeSafe($cid).' ORDER BY rid DESC ;');

?>
<table>
	<caption>Rounds</caption>
	<thead>
		<tr>
			<th>No</th>
			<th>Round Name</th>
			<th>DEL</th>
		</tr>
	</thead>
	<tbody>
<?php
while($row = dbFetchRow($result)) {
	$rid = $row['rid'];
?>		<tr id="<?php echo 'R'.$rid;?>">
			<td><?php echo $rid; ?></td>
			<td><?php echo $row['name'];?>
			<td><div id="<?php echo 'D'.$rid; ?>" class="del"></div></td>
		</tr>
<?php
}
dbFree($result);

?>	</tbody>
</table>
