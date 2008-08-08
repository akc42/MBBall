<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['rid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
$cid = $_GET['cid'];
$rid = $_GET['rid'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
if( $cid !=0) {

	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');
?><table>
	<caption>Teams</caption>
	<thead>
		<tr>
			<th class="team">TiC</th>
			<th class="team">Teams</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td id="tic">
<?php
	$sql = 'SELECT tid FROM team_in_competition WHERE cid = '.dbMakeSafe($cid);
	$sql .= ' EXCEPT SELECT (SELECT hid AS tid FROM match UNION SELECT aid AS tid FROM match';
	$sql .= ' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid);
	$sql .= ') ORDER BY tid;';
	$result = dbQuery($sql);
	while($row = dbFetchRow($result)) {
?>	<div class="tid"><?php echo $row['tid'];?></div>
<?php
	}
	dbFree($result);
?>			</td>
			<td id="tnic">
<?php
	$sql = 'SELECT t.tid FROM team t EXCEPT SELECT c.tid FROM team_in_competition c WHERE c.cid = ';
	$sql .= dbMakeSafe($cid).' ORDER BY tid;';
	$result = dbQuery($sql);
	while($row = dbFetchRow($result)) {
?>	<div class="tid"><?php echo $row['tid'];?></div>
<?php
	}
	dbFree($result);
?>			</td>
		</tr>
	</tbody>
</table>
<input id="addall" type="button" value="&lt;&lt; Add All"/>  
<?php
} else {
?><p>No Team information available right now</p>
<?php
}