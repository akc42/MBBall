<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];
if ($cid != 0) {
	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');
	$result = dbQuery('SELECT rid,name,ou_round,open FROM round WHERE cid = '.dbMakeSafe($cid).' ORDER BY rid DESC ;');
	
?>
<table>
	<caption>Rounds</caption>
	<thead>
		<tr>
			<th class="radio">No</th>
			<th>Round Name</th>
			<th class="del_head">DEL</th>
		</tr>
	</thead>
	<tbody>
<?php
	while($row = dbFetchRow($result)) {
		$rid = $row['rid'];
?>		<tr>
			<td id="<?php echo 'R'.$rid;?>" class="selectthis"><?php echo $rid; ?></td>
			<td id="<?php echo 'S'.$rid;?>" class="selectthis"><?php echo $row['name'];?></td>
			<td><div id="<?php echo 'E'.$rid; ?>" class="del">
					<input type="hidden" name="open" value="<?php echo ($row['open'] == 't')?$rid:0;?>" /></div></td>
		</tr>
<?php
	}
	dbFree($result);

?>	</tbody>
</table>
<?php
} else {
?><p>No Round Data</p>
<?php
}
?>
