<?php
if(!(isset($_GET['uid']) && isset($_GET['pass'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');

$resultusers = dbQuery('SELECT uid,name FROM participant WHERE last_logon > extract(epoch from now()) - 31536000 AND is_bb IS NOT TRUE
				ORDER BY admin_experience DESC, name;');
$userdata = dbFetch($resultusers);

$sql = 'SELECT cid,description,uid,name FROM competition c LEFT JOIN participant p ON c.administrator = p.uid';
if(!isset($_GET['global'])) {
	// When not global administrator, only see competitions for which are administrator
	$sql .= ' WHERE c.administrator ='.dbMakeSafe($uid);
}
$sql .= ' ORDER BY cid DESC ;';
$result = dbQuery($sql);
?>	<form id="default_competition" action="setdefault.php">
		<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
		<input type="hidden" name="pass" value="<?php echo $password; ?>" />
		<table>
			<caption>Football Competitions</caption>
			<thead>
				<tr>
					<th class="ctitle">Title</th>
					<th class="user">Competition Administrator</th>
					<th class="option1">Default</th>
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
		$dcid = $row['cid'];
	} else {
		$dcid = 0;
	}

	while($row = dbFetchRow($result)) {
?>
				<tr>
					<td id="<?php echo 'C'.$row['cid'];?>" class="selectthis"><?php echo $row['description']; ?></td>
					<td id="<?php echo 'A'.$row['cid'];?>" class="selectthis"><?php echo $row['name'];?></td>
					<td>
						<input class="default" type="radio" name="defcomps" value="<?php echo $row['cid'];?>" 
							<?php if($dcid == $row['cid']) echo 'checked="checked"' ;?> />
					</td>
					<td><div id="<?php echo 'D'.$row['cid']; ?>" class="del"></div></td>
				</tr>
<?php
	}
}
dbFree($result);
?>			</tbody>
		</table>
	</form>
<hr/>
<p id="compserr"></p>
	<form id="createform" action="createcomp.php">
		<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
		<input type="hidden" name="pass" value="<?php echo $password; ?>" />
		<table class="form">
			<caption>Create Competition</caption>
			<tbody>
				<tr>
					<td>
			<label>Competition Title<br/>
			<input id="desc" name="desc" type="text" class="ctitle" value="<?php echo $comp['description'];?>" /></label>
					</td>
					<td>
			<label>Administrator<br/>
			<select id="adm" name="adm" class="user">
<?php
	foreach($userdata as $user) {
?>				<option value="<?php echo $user['uid'];?>"
                    <?php if ($user['uid'] == $uid) echo 'selected="selected"' ;?>><?php echo $user['name'] ;?></option>
<?php
	}
?>			</select></label>
					</td>
					<td class="option1">
			<label><input id="def" type="checkbox" name="setdefault" value="set" />Set as default</label>
					</td>
					<td class="submit">
						<input type="submit" value="Create" />
					</td>
				</tr>
			</tbody>
		</table>
	</form>
