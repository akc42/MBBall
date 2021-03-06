<?php
/*
    Copyright (c) 2010 Alan Chandler
    This file is part of MBBall, an American Football Results Picking
    Competition Management software suite.

    MBBall is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    MBBall is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with MBBall (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.

*/

/*

	USAGE.
	
	1. Copy this file from the supporting directory into the application directory
	2. Alter the database connection parameters below to match the Postgres database 
		you are connecting to
*/

$db_server = 'localhost';
$db_name = 'melindas_ball';
$db_user = 'melindas_ball';
$db_password = 'xxxxxx';

/*
	3. In a web browser call up dump.php (at the appropriate url).
	
	RESULT
	
	This should create footballpg.db in the normal data directory.  It can be renamed
	football.db so it can be used in the main application
*/




// This is a copy of the OLD db.inc so its stand alone and will work in the new environment

pg_connect("host=$db_server dbname=$db_name user=$db_user password=$db_password")
		or die('Could not connect to database: ' . pg_last_error());
function dbQuery($sql) {
	$result = pg_query($sql);
	if (!$result) {
		echo '<tt>';
		echo "<br/><br/>\n";
		print_r(debug_backtrace());
		echo "<br/><br/>\n";
		echo $sql;
		echo "<br/><br/>\n\n";
		echo pg_last_error();
		echo "<br/><br/>\n\n";
		echo '</tt>';
		die('<p>Please inform <i>webmaster@melindasbackups.com</i> that a database query failed in the football and include the above text.<br/><br/>Thank You</p>');
	}
	return $result;
}
function dbMakeSafe($value) {
	if (!get_magic_quotes_gpc()) {
		$value=pg_escape_string($value);
	}
	return "'".$value."'" ;
}
function dbPostSafe($text) {
  if ($text == '') return 'NULL';
  return dbMakeSafe(htmlentities($text,ENT_QUOTES,'UTF-8',false));
}
function dbNumRows($result) {
	return pg_num_rows($result);
}
function dbFetchRow($result) {
	return pg_fetch_assoc($result);
}
function dbFree($result){
	pg_free_result($result);
}
function dbRestartQuery($result) {
	pg_result_seek($result,0);
}
function dbFetch($result) {
	return pg_fetch_all($result);
}

//START of dump program proper


echo "<p>Starting Information Transfer<br/>\n";

if (file_exists('footballpg.db')) unlink('footballpg.db');
$db=new PDO('sqlite:footballpg.db');
$db->exec(file_get_contents('database.sql'));  //setup database with initial settings

dbQuery('BEGIN;');
$result=dbQuery("SELECT cid FROM default_competition LIMIT 1");
$row = dbFetchRow($result);
$cid = $row['cid'];

$db->exec('BEGIN EXCLUSIVE');
$f=$db->prepare("UPDATE settings SET value = ? WHERE name = 'default_competition'");
$f->bindValue(1,$cid,PDO::PARAM_INT);
$f->execute();
$f->closeCursor();
unset($f);

dbFree($result);
echo "done settings <br/>\n";

/*
 * Set up competition table
 */

$result = dbQuery("SELECT *, date_part('epoch',creation_date) AS cd FROM competition");
$c=$db->prepare("
			INSERT INTO competition(cid,description,condition,administrator,open,pp_deadline,gap,guest_approval,creation_date)
			VALUES (?,?,?,?,?,?,?,?,?)
			");
while($row = dbFetchRow($result)) {
		$c->bindValue(1,$row['cid']);
		$c->bindValue(2,$row['description']);
		$c->bindValue(3,$row['condition']);
		$c->bindValue(4,$row['administrator'],PDO::PARAM_INT);
		$c->bindValue(5,($row['open'] == 't')?1:0,PDO::PARAM_INT);
		$c->bindValue(6,$row['pp_deadline'],PDO::PARAM_INT);
		$c->bindValue(7,$row['gap'],PDO::PARAM_INT);
		$c->bindValue(8,($row['bb_approval'] == 't')?1:0,PDO::PARAM_INT);
		$c->bindValue(9,$row['cd'],PDO::PARAM_INT);
		$c->execute();
		$c->closeCursor();
}
dbFree($result);
unset($c);
echo "done competition <br/>\n";
/*
	Setup participant table
*/
$result=dbQuery("SELECT * FROM participant ");
$p=$db->prepare("
	INSERT INTO participant(uid,name,email,password,last_logon,admin_experience,is_guest)
	VALUES (?,?,?,null,?,?,?)
	");
while($r2=dbFetchRow($result)) {
	$p->bindValue(1,$r2['uid'],PDO::PARAM_INT);
	$p->bindValue(2,$r2['name']);
	$p->bindValue(3,$r2['email']);
	$p->bindValue(4,$r2['last_logon'],PDO::PARAM_INT);
	$p->bindValue(5,($r2['admin_experience'] == 't')?1:0,PDO::PARAM_INT);
	$p->bindValue(6,($r2['is_bb'] == 't')?1:0,PDO::PARAM_INT);
	$p->execute();
	$p->closeCursor();
}
dbFree($result);
unset($p);
echo "done participant <br/>\n";
/*
	Setup registration table
*/
$result=dbQuery("SELECT * FROM registration ");
$r=$db->prepare("
	INSERT INTO registration(cid,uid,agree_time,approved)
	VALUES (?,?,?,?)
	");
while($r2=dbFetchRow($result)) {
	$r->bindValue(1,$r2['cid'],PDO::PARAM_INT);
	$r->bindValue(2,$r2['uid'],PDO::PARAM_INT);
	$r->bindValue(3,$r2['agree_time'],PDO::PARAM_INT);
	$r->bindValue(4,($r2['bb_approved'] == 't')?1:0,PDO::PARAM_INT);
	$r->execute();
	$r->closeCursor();
}
dbFree($result);
unset($r);
echo "done registration <br/>\n";
/*
	Setup Rounds
*/
$result=dbQuery("SELECT * FROM round");
$r=$db->prepare("
	INSERT INTO round(cid,rid,question,valid_question,answer,value,name,ou_round,deadline,open)
	VALUES (?,?,?,?,?,?,?,?,?,?)
	");
while($r2=dbFetchRow($result)) {
	$r->bindValue(1,$r2['cid'],PDO::PARAM_INT);
	$r->bindValue(2,$r2['rid'],PDO::PARAM_INT);
	$r->bindValue(3,$r2['question']);
	$r->bindValue(4,($r2['valid_question'] == 't')?1:0,PDO::PARAM_INT);
	$r->bindValue(5,$r2['answer'],PDO::PARAM_INT);
	$r->bindValue(6,$r2['value'],PDO::PARAM_INT);
	$r->bindValue(7,$r2['name']);
	$r->bindValue(8,($r2['ou_round'] == 't')?1:0,PDO::PARAM_INT);
	$r->bindValue(9,$r2['deadline'],PDO::PARAM_INT);
	$r->bindValue(10,($r2['open'] == 't')?1:0,PDO::PARAM_INT);
	$r->execute();
	$r->closeCursor();
}
dbFree($result);
unset($r);
echo "done round <br/>\n";
/*
	Setup Matches
*/
$result=dbQuery("SELECT * FROM match");
$m=$db->prepare("
	INSERT INTO match(cid,rid,hid,aid,comment,ascore,hscore,combined_score,open,match_time)
	VALUES (?,?,?,?,?,?,?,?,?,?)
	");
while($r2=dbFetchRow($result)) {
	$m->bindValue(1,$r2['cid'],PDO::PARAM_INT);
	$m->bindValue(2,$r2['rid'],PDO::PARAM_INT);
	$m->bindValue(3,trim($r2['aid'])); //NOTE: These are switched round in new setup
	$m->bindValue(4,trim($r2['hid'])); //NOTE: These are switched round in new setup
	$m->bindValue(5,$r2['comment']);
	$m->bindValue(6,$r2['hscore'],PDO::PARAM_INT);//NOTE: These are switched round in new setup
	$m->bindValue(7,$r2['ascore'],PDO::PARAM_INT);//NOTE: These are switched round in new setup
	$m->bindValue(8,$r2['combined_score'],PDO::PARAM_INT);
	$m->bindValue(9,($r2['open'] == 't')?1:0,PDO::PARAM_INT);
	$m->bindValue(10,$r2['match_time'],PDO::PARAM_INT);
	$m->execute();
	$m->closeCursor();
}
dbFree($result);
unset($m);
echo "done match <br/>\n";
/*
	Setup Option
*/
$result=dbQuery("SELECT * FROM option");
$o=$db->prepare("INSERT INTO option(cid,rid,opid,label) VALUES (?,?,?,?);");
while($r2=dbFetchRow($result)) {
	$o->bindValue(1,$r2['cid'],PDO::PARAM_INT);
	$o->bindValue(2,$r2['rid'],PDO::PARAM_INT);
	$o->bindValue(3,$r2['opid'],PDO::PARAM_INT);
	$o->bindValue(4,$r2['label']);
	$o->execute();
	$o->closeCursor();
}
dbFree($result);
unset($o);
echo "done option <br/>\n";
/*
	Setup Team In Competition (Team already setup)
*/
$result=dbQuery("SELECT * FROM team_in_competition ");
$t=$db->prepare("INSERT INTO team_in_competition(cid,tid,made_playoff) VALUES (?,?,?)");
while($r2=dbFetchRow($result)) {
	$t->bindValue(1,$r2['cid'],PDO::PARAM_INT);	
	$t->bindValue(2,trim($r2['tid']));
	$t->bindValue(3,($r2['made_playoff'] == 't')?1:0,PDO::PARAM_INT);
	$t->execute();
	$t->closeCursor();
}
dbFree($result);
unset($t);
echo "done tic <br/>\n";
/*
	Setup Match Picks
*/
$result=dbQuery("SELECT * FROM pick");
$p=$db->prepare("INSERT INTO pick(cid,uid,rid,aid,comment,pid,over_selected,submit_time) VALUES (?,?,?,?,?,?,?,?)");
while($r2=dbFetchRow($result)) {
	$p->bindValue(1,$r2['cid'],PDO::PARAM_INT);
	$p->bindValue(2,$r2['uid'],PDO::PARAM_INT);
	$p->bindValue(3,$r2['rid'],PDO::PARAM_INT);
	$p->bindValue(4,trim($r2['hid']));//NOTE: These are switched to aid
	$p->bindValue(5,$r2['comment']);
	$p->bindValue(6,trim($r2['pid']));
	$p->bindValue(7,($r2['over_selected'] == 't')?1:0,PDO::PARAM_INT);
	$p->bindValue(8,$r2['submit_time'],PDO::PARAM_INT);
	$p->execute();
	$p->closeCursor();
}
dbFree($result);
unset($p);
echo "done pick <br/>\n";
/*
	Setup Divisional Winner Picks
*/
$result=dbQuery("SELECT * FROM div_winner_pick");
$p=$db->prepare("INSERT INTO div_winner_pick(cid,uid,confid,divid,tid,submit_time) VALUES (?,?,?,?,?,?)");
while($r2=dbFetchRow($result)) {
	$p->bindValue(1,$r2['cid'],PDO::PARAM_INT);
	$p->bindValue(2,$r2['uid'],PDO::PARAM_INT);
	$p->bindValue(3,$r2['confid']);
	$p->bindValue(4,$r2['divid']);
	$p->bindValue(5,trim($r2['tid']));
	$p->bindValue(6,$r2['submit_time'],PDO::PARAM_INT);
	$p->execute();
	$p->closeCursor();
}
dbFree($result);
unset($p);
echo "done div winner pick <br/>\n";
/*
	Setup WildCard Picks
*/
$result=dbQuery("SELECT * FROM wildcard_pick");
$p=$db->prepare("INSERT INTO wildcard_pick(cid,uid,confid,opid,tid,submit_time) VALUES (?,?,?,?,?,?)");
while($r2=dbFetchRow($result)) {
	$p->bindValue(1,$r2['cid'],PDO::PARAM_INT);
	$p->bindValue(2,$r2['uid'],PDO::PARAM_INT);
	$p->bindValue(3,$r2['confid']);
	$p->bindValue(4,$r2['opid'],PDO::PARAM_INT);
	$p->bindValue(5,trim($r2['tid']));
	$p->bindValue(6,$r2['submit_time'],PDO::PARAM_INT);
	$p->execute();
	$p->closeCursor();
}
dbFree($result);
unset($p);
echo "Done wildcardpick <br/>\n";
/*
	Setup Option Picks
*/
$result=dbQuery("SELECT * FROM option_pick");
$p=$db->prepare("INSERT INTO option_pick(cid,uid,rid,opid,comment,submit_time) VALUES (?,?,?,?,?,?)");
while($r2=dbFetchRow($result)) {
	$p->bindValue(1,$r2['cid'],PDO::PARAM_INT);
	$p->bindValue(2,$r2['uid'],PDO::PARAM_INT);
	$p->bindValue(3,$r2['rid'],PDO::PARAM_INT);
	$p->bindValue(4,$r2['opid'],PDO::PARAM_INT);
	$p->bindValue(5,$r2['comment']);
	$p->bindValue(6,$r2['submit_time'],PDO::PARAM_INT);
	$p->execute();
	$p->closeCursor();
}
dbFree($result);
unset($p);
echo "Done option pick <br/>\n";
$db->exec("COMMIT");
$db = null; //closes the database
echo "COMPLETE</p>\n";		


