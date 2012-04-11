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
	
	1. Place this file in the application directory of the old (Postgres based) version of football
	2. Create a subdirectory called db and copy database.sql and football.sql from the app/inc directory there
	3. In a web browser call up dump.php (at the appropriate url).
	
	RESULT
	
	This should create football.ini and a set of .db files representing each of the competitions that were contained on the main database
*/



$db=new PDO('sqlite:./db/football.ini');
$db->exec(file_get_contents('./db/football.sql'));  //setup first database
$db = null;

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.inc');

dbQuery('BEGIN;');
$result=dbQuery("SELECT cid FROM default_competition LIMIT 1");
$row = dbFetchRow($result);
$cid = $row['cid'];
dbFree($result);

$result = dbQuery("SELECT *, date_part('epoch',creation_date) AS cd FROM competition");
while($row = dbFetchRow($result)) {
	$filename = './db/'.str_replace(Array(" ","."),Array('-','_'),$row['description']);
	if(!file_exists($filename.'.db')) {
		/*
			Setup the main competition section of the database
		*/
		$newcid = uniqid(); //Generate a uniqid for this competition
		$db = new PDO('sqlite:'.$filename.'.db');
		$db->exec(file_get_contents('./db/database.sql')); //setup the database (this is within its own transaction)
		if($row['cid'] == $cid) {
			$db->exec("ATTACH './db/football.ini' AS football");
			$f=$db->prepare("UPDATE football.config SET default_competition = ?, admin_key = NULL");
			$f->bindValue(1,$newcid);
			$f->execute();
			$f->closeCursor();
			unset($f);
			$db->exec("DETACH football");
		}
		$db->exec("BEGIN EXCLUSIVE");
		$c=$db->prepare("
			INSERT INTO competition(cid,description,condition,administrator,open,pp_deadline,gap,bb_approval,creation_date,dversion)
			VALUES (?,?,?,?,?,?,?,?,?,1)
			");
		$c->bindValue(1,$newcid);  //Give this competition a unique id (as near as possible)
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
		unset($c);
		/*
			Setup participant table
		*/
		$table=dbQuery("SELECT u.*, agree_time,bb_approved FROM participant u JOIN registration r USING (uid) WHERE r.cid = ".$row['cid']);
		$p=$db->prepare("
			INSERT INTO participant(uid,name,email,password,last_logon,admin_experience,is_guest,agree_time,approved)
			VALUES (?,?,?,null,?,?,?,?,?)
			");
		while($r2=dbFetchRow($table)) {
			$p->bindValue(1,$r2['uid'],PDO::PARAM_INT);
			$p->bindValue(2,$r2['name']);
			$p->bindValue(3,$r2['email']);
			$p->bindValue(4,$r2['last_logon'],PDO::PARAM_INT);
			$p->bindValue(5,($r2['admin_experience'] == 't')?1:0,PDO::PARAM_INT);
			$p->bindValue(6,($r2['is_bb'] == 't')?1:0,PDO::PARAM_INT);
			$p->bindValue(7,$r2['agree_time'],PDO::PARAM_INT);
			$p->bindValue(8,($r2['approved'] == 't')?1:0,PDO::PARAM_INT);
			$p->execute();
			$p->closeCursor();
		}
		dbFree($table);
		unset($p);
		/*
			Setup Rounds
		*/
		$table=dbQuery("SELECT * FROM round WHERE cid = ".$row['cid']);
		$r=$db->prepare("
			INSERT INTO round(rid,question,valid_question,answer,value,name,ou_round,deadline,open)
			VALUES (?,?,?,?,?,?,?,?,?)
			");
		while($r2=dbFetchRow($table)) {
			$r->bindValue(1,$r2['rid'],PDO::PARAM_INT);
			$r->bindValue(2,$r2['question']);
			$r->bindValue(3,($r2['valid_question'] == 't')?1:0,PDO::PARAM_INT);
			$r->bindValue(4,$r2['answer'],PDO::PARAM_INT);
			$r->bindValue(5,$r2['value'],PDO::PARAM_INT);
			$r->bindValue(6,$r2['name']);
			$r->bindValue(7,($r2['ou_round'] == 't')?1:0,PDO::PARAM_INT);
			$r->bindValue(8,$r2['deadline'],PDO::PARAM_INT);
			$r->bindValue(9,($r2['open'] == 't')?1:0,PDO::PARAM_INT);
			$r->execute();
			$r->closeCursor();
		}
		dbFree($table);
		unset($r);
		/*
			Setup Matches
		*/
		$table=dbQuery("SELECT * FROM match WHERE cid = ".$row['cid']);
		$m=$db->prepare("
			INSERT INTO match(rid,hid,aid,comment,ascore,hscore,combined_score,open,match_time)
			VALUES (?,?,?,?,?,?,?,?,?)
			");
		while($r2=dbFetchRow($table)) {
			$m->bindValue(1,$r2['rid'],PDO::PARAM_INT);
			$m->bindValue(2,$r2['hid']);
			$m->bindValue(3,$r2['aid']);
			$m->bindValue(4,$r2['comment']);
			$m->bindValue(5,$r2['ascore'],PDO::PARAM_INT);
			$m->bindValue(6,$r2['hscore'],PDO::PARAM_INT);
			$m->bindValue(7,$r2['combined_score'],PDO::PARAM_INT);
			$m->bindValue(8,($r2['open'] == 't')?1:0,PDO::PARAM_INT);
			$m->bindValue(9,$r2['match_time'],PDO::PARAM_INT);
			$m->execute();
			$m->closeCursor();
		}
		dbFree($table);
		unset($m);
		/*
			Setup Option
		*/
		$table=dbQuery("SELECT * FROM option WHERE cid = ".$row['cid']);
		$o=$db->prepare("INSERT INTO option(rid,opid,label) VALUES (?,?,?);");
		while($r2=dbFetchRow($table)) {
			$o->bindValue(1,$r2['rid'],PDO::PARAM_INT);
			$o->bindValue(2,$r2['opid'],PDO::PARAM_INT);
			$o->bindValue(3,$r2['label']);
			$o->execute();
			$o->closeCursor();
		}
		dbFree($table);
		unset($o);
		/*
			Fixup (ie just adjust) Team
		*/
		$table=dbQuery("SELECT t.*,made_playoff FROM team t LEFT JOIN team_in_competition USING (tid) WHERE team_in_competition.cid = ".$row['cid']);
		$t=$db->prepare("UPDATE team SET in_competition = ? , made_playoff = ? WHERE tid = ?");
		while($r2=dbFetchRow($table)) {
			$t->bindValue(1,(is_null($r2['made_playoff']))?0:1,PDO::PARAM_INT);
			$t->bindValue(2,($r2['made_playoff'] == 't')?1:0,PDO::PARAM_INT);
			$t->bindValue(3,$r2['tid']);
			$t->execute();
			$t->closeCursor();
		}
		dbFree($table);
		unset($t);
		/*
			Setup Match Picks
		*/
		$table=dbQuery("SELECT * FROM pick WHERE cid = ".$row['cid']);
		$p=$db->prepare("INSERT INTO pick(uid,rid,hid,comment,pid,over_selected,submit_time) VALUES (?,?,?,?,?,?,?)");
		while($r2=dbFetchRow($table)) {
			$p->bindValue(1,$r2['uid'],PDO::PARAM_INT);
			$p->bindValue(2,$r2['rid'],PDO::PARAM_INT);
			$p->bindValue(3,$r2['hid']);
			$p->bindValue(4,$r2['comment']);
			$p->bindValue(5,$r2['pid']);
			$p->bindValue(6,($r2['over_selected'] == 't')?1:0,PDO::PARAM_INT);
			$p->bindValue(7,$r2['submit_time'],PDO::PARAM_INT);
			$p->execute();
			$p->closeCursor();
		}
		dbFree($table);
		/*
			Setup Divisional Winner Picks
		*/
		$table=dbQuery("SELECT * FROM div_winner_pick WHERE cid = ".$row['cid']);
		$p=$db->prepare("INSERT INTO div_winner_pick(uid,confid,divid,tid,submit_time) VALUES (?,?,?,?,?)");
		while($r2=dbFetchRow($table)) {
			$p->bindValue(1,$r2['uid'],PDO::PARAM_INT);
			$p->bindValue(2,$r2['confid']);
			$p->bindValue(3,$r2['divid']);
			$p->bindValue(4,$r2['tid']);
			$p->bindValue(5,$r2['submit_time'],PDO::PARAM_INT);
			$p->execute();
			$p->closeCursor();
		}
		dbFree($table);
		/*
			Setup WildCard Picks
		*/
		$table=dbQuery("SELECT * FROM wildcard_pick WHERE cid = ".$row['cid']);
		$p=$db->prepare("INSERT INTO wildcard_pick(uid,confid,opid,tid,submit_time) VALUES (?,?,?,?,?)");
		while($r2=dbFetchRow($table)) {
			$p->bindValue(1,$r2['uid'],PDO::PARAM_INT);
			$p->bindValue(2,$r2['confid']);
			$p->bindValue(3,$r2['opid'],PDO::PARAM_INT);
			$p->bindValue(4,$r2['tid']);
			$p->bindValue(5,$r2['submit_time'],PDO::PARAM_INT);
			$p->execute();
			$p->closeCursor();
		}
		dbFree($table);
		/*
			Setup Option Picks
		*/
		$table=dbQuery("SELECT * FROM option_pick WHERE cid = ".$row['cid']);
		$p=$db->prepare("INSERT INTO option_pick(uid,rid,opid,comment,submit_time) VALUES (?,?,?,?,?)");
		while($r2=dbFetchRow($table)) {
			$p->bindValue(1,$r2['uid'],PDO::PARAM_INT);
			$p->bindValue(2,$r2['rid'],PDO::PARAM_INT);
			$p->bindValue(3,$r2['opid'],PDO::PARAM_INT);
			$p->bindValue(4,$r2['comment']);
			$p->bindValue(5,$r2['submit_time'],PDO::PARAM_INT);
			$p->execute();
			$p->closeCursor();
		}
		dbFree($table);
		unset($p);
		$db->exec("COMMIT");
		$db = null; //closes the database		
	} else {
		echo "The database $filename already exists\n";
	}
}
dbFree($result);
dbQuery('ROLLBACK');

