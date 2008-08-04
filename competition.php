<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['cid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
$cid = $_GET['cid'];

if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$resultcomp=dbQuery('SELECT * FROM competition WHERE cid = '.dbMakeSafe($cid).';');
if($comp = dbFetchRow($resultcomp)) {
?><div id="teams">
	<h1>Teams in The Competition</h1>
	<div class="holder"> 
		<h2>Currently Selected Teams</h2> 
		<form id="ticform" action="updatetic.php" >
			<select multiple="multiple" id="tic" name="tic[]">
<?php
	$sql = 'SELECT t.tid ,t.name FROM team_in_competition c JOIN team t USING (tid) WHERE c.cid = ';
	$sql .= dbMakeSafe($cid).' ORDER BY name;';
	$result = dbQuery($sql);
	while($row = dbFetchRow($result)) {
?>				<option value="<?php echo $row['tid'];?>"><?php echo $row['name'].' ('.$row['tid'].')' ;?></option>
<?php
	}
	dbFree($result);
?>			</select>  
			<input type="button" value="Remove &gt;" />
			<input type="submit" value="Save" />
		</form>
	</div>
	<div class="holder">
		<h2>Teams Not Yet Selected</h2> 
		<select multiple="multiple" id="tnic" name="tnic[]">
<?php
	$sql = 'SELECT t.tid,t.name FROM team t EXCEPT SELECT t.tid ,t.name FROM (team_in_competition c JOIN team t USING (tid)) WHERE c.cid = ';
	$sql .= dbMakeSafe($cid).' ORDER BY name;';
	$result = dbQuery($sql);
	while($row = dbFetchRow($result)) {
?>			<option value="<?php echo $row['tid'];?>"><?php echo $row['name'].' ('.$row['tid'].')' ;?></option>
<?php
	}
	dbFree($result);
?>		</select>  
		<input id="add" type="button" value="&lt; Add"/>
		<input id="addall" type="button" value="&lt;&lt; Add All"/>  
	</div>  
</div>  
<div id="compdetail">
	<a href="#" id="competitions">Return to Competition List</a>
	<form id="compform" action="updatecomp.php" >
		<input type="hidden" name="uid" value="<?php echo $uid;?>" />
		<input type="hidden" name="pass" value="<?php echo $pass;?>" />
		<input type="hidden" name="cid" value="<?php echo $cid;?>" />	
		<div id="adminselect">
			<select id="adm" name="adm">
<?php
	$resultusers = dbQuery('SELECT uid,name FROM participant WHERE last_logon > now() - interval \'1 year 1 month\' AND is_bb IS NOT TRUE
				ORDER BY admin_experience DESC, name;');
	$userdata = dbFetch($resultusers);
	dbFree($resultusers);
	foreach($userdata as $user) {
?>				<option value="<?php echo $user['uid'];?>" 
					<?php if ($user['uid'] == $comp['administrator']) echo 'selected="selected"' ;?>>
						<?php echo $user['name'] ;?></option>
<?php
	}
?>			</select>
		</div>
		<div id="title" class="title">
			<label>Title<input id="desc" name="desc" type="text" value="<?php echo $comp['description'];?>" /></label>
		</div>
		<div id="condition">
			<textarea id="cond"><?php echo $comp['condition'];?></textarea>
		</div>
		<div id="open">
			<label><input id="openchk" name="openchk" type="checkbox" value="set" 
				<?php if ($comp['open'] == 't') echo 'checked="checked"' ;?>/>Open</label>
		</div>
		<div id="bbapprove">
			<label><input id="bbchk" name="bbchk" type="checkbox" value="set"
				<?php if ($comp['bb_approval'] == 't') echo 'checked="checked"' ;?>/>BB's need Approval</label>
		</div>
		<div id="playoffdeadline">
			<label>Playoff Selection Deadline (leave blank for no playoff selection)<br/>
			<input id ="ppdead" name="ppdead" class="time" value="<?php echo $comp['pp_deadline'];?>" />
		</div>
		<input type="submit" value="Update" />
	</form>
<div>
<div style="clear:both">&nbsp;</div>
<hr />
<div id="rounddata"></div>
<?php
} else {
?><p> Selected Competition Data is Not Available. <a id="competitions" href="#">Click here</a> to return to list of Competitions</p>
<?php
}
dbFree($resultcomp);
