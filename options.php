<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['answer'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
$cid = $_GET['cid'];
$rid = $_GET['rid'];
if($rid != 0 && $cid !=0) {

	define ('BALL',1);   //defined so we can control access to some of the files.
	require_once('db.php');
	
	$optionresult = dbQuery('SELECT * FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' ORDER BY opid;');
	$noopts = 0;
?><table>
	<caption>Answer Options</caption>
	<tbody>
<?php
	while($row = dbFetchRow($optionresult)) {
		$noopts = max($noopts,$row['opid']);
?>	<tr>
		<td>
			<label>
				<input type="radio" value="<?php echo $row['oid'];?>" name="option"
					<?php if($row['opid'] == $_GET['answer']) echo 'checked="checked"';?> />
				<span><?php echo $row['label'];?></span>
			</label>
		</td>
	 
	</tr>
<?php
	}
	dbFree($optionresult);
?>	<tr class="hidden">
		<td><label><input type="radio" name="option" value="<?php echo $noopts+1;?>" /><span></span></label></td>
	</tr>
	</tbody>
</table>
<?php
} else {
?><p>No Option information available right now</p>
<?php
}


