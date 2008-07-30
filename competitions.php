<?php
if(!(isset($_GET['uid']) && isset($_GET['pass'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
if ($_GET['pass'] != sha1("Football".$uid))
	die('Hacking attempt got: '.$_GET['pass'].' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$resultusers = dbQuery('SELECT uid,name FROM participant WHERE last_logon > now() - interval \'1 year 1 month\' AND is_bb IS NOT TRUE
				ORDER BY admin_experience DESC, name;');
$userdata = dbFetch($resultusers);
$result = dbQuery('SELECT description,uid,name FROM competition c LEFT JOIN participant p ON c.administrator = p.uid ORDER BY cid DESC  ;');
?>	<form id="default_competition" action="setdefault.php?<?php echo 'uid='.$uid.'&pass='.$password;?>">
		<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
		<input type="hidden" name="pass" value="<?php echo $password; ?>" />
		<table>
			<caption>Football Competitions</caption>
			<thead>
				<tr>
					<th>Title</th>
					<th>Competition Administrator</th>
					<th>Default</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
<?php
if(dbNumRows($result) > 0) {
	$resultdefault = dbQuery('SELECT cid FROM default_competition;');
	if(dbNumRows($resultdefault) != 1 ) {
		die("Database is corrupt - default_competition should have a single row");
	}
	$row=dbFetchRow($resultdefault);
	if (!is_null($row['cid'])) {
		$cid = $row['cid'];
	} else {
		$cid = 0;
	}

	while($row = dbFetchRow($result)) {
?>
				<tr>
					<td id="<?php echo 'D'.$row['cid'];?>" class="clickable"><?php echo $row['description']; ?></td>
					<td id="<?php echo 'U'.$row['cid'];?>" class="clickable"><?php echo $row['name'];?></td>
					<td>
						<input type="radio" name="default" value="<?php echo $row['cid'];?>" 
							<?php if($cid == $row['cid']) echo 'checked="checked"' ;?> />
					</td>
					<td></td>
				</tr>
<?php
	}
}
?>			</tbody>
		</table>
	</form>
<hr/>
	<form id="update" action="updatecomp.php?<?php echo 'uid='.$uid.'&pass='.$password;?>">
		<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
		<input type="hidden" name="pass" value="<?php echo $password; ?>" />
		<input id = "cid" type="hidden" name="cid" value="0"/>
		<table>
			<tbody>
				<tr>
					<td><input id="desc" type="text" name="Dcreate" value="" /></td>
					<td>
						<select name="Ucreate">
<?php
	foreach($userdata as $user) {
?>							<option value="<?php echo $user['uid'];?>" 
									<?php if ($user['uid'] == $uid) echo 'selected="selected"' ;?>>
								<?php echo $user['name'] ;?>
							</option>
<?php
	}
?>						</select>
					</td>
					<td><input id="Ccreate" type="checkbox" name="setdefault" value="set" checked="checked" /></td>
					<td><input id="Screate" type="submit" value="Create" /></td>
				</tr>
			</tbody>
		</table>
	</form>
