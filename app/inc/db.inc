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
if(!defined('DATA_DIR')) define('DATA_DIR','/home/alan/dev/football/db/');
if(!defined('PRIVATE_KEY')) define('PRIVATE_KEY','Football19Key');

function splitFilename($filename) {
	$pos = strrpos($filename, '.');
	if ($pos === false) { // dot is not found in the filename
		return array($filename, ''); // no extension
	} else {
		$basename = substr($filename, 0, $pos);
		$extension = substr($filename, $pos+1);
		return array($basename, $extension);
	} 
}

//This is a convenient place to force everything we output to not be cached (even 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// $cid must be set before calling db.inc

$db=new PDO('sqlite:'.DATA_DIR.'football.ini');
$c=$db->prepare("SELECT filename FROM cache WHERE cid = ?");
$c->bindValue(1,$cid);
$c->execute();
$filename = $c->fetchColumn();
$c->closeCursor();
if(!$filename) {
	//cid not in cache - we need to rebuild it
	$db->exec("DELETE FROM cache"); //remove old cache
	$fns = scandir(DATA_DIR);
	$result=null;
	$foundcid = false;
	$cidfn = '';
	foreach ($fns as $filename) {
		if(filetype(DATA_DIR.$filename) == 'file') {
			$split = splitFilename($filename);
			if($split[1] == 'db') {
			// found a database file
				$astmt = $db->prepare("ATTACH ? AS comp");
				$astmt->bindValue(1,DATA_DIR.$filename);
				$astmt->execute(); //ATTACH to the database
				$bstmt = $db->prepare("INSERT INTO cache(cid,filename,creation_date,description) VALUES (?,?,?,?)");
				$db->exec("BEGIN IMMEDIATE");
				$result=$db->query("SELECT cid, description, creation_date FROM comp.competition");
				if($row=$result->fetch(PDO::FETCH_ASSOC)) {
					$bstmt->bindValue(1,$row['cid']);
					$bstmt->bindValue(2,$split[0]);
					$bstmt->bindValue(3,$row['creation_date'],PDO::PARAM_INT);
					$bstmt->bindValue(4,$row['description']);	
					$bstmt->execute();
					$bstmt->closeCursor();
					if($row['cid'] == $cid) {
						$foundcid = true;
						$cidfn = $split[0];
					}
				} 
				$result->closeCursor();
				$db->exec("COMMIT");
				$astmt->closeCursor();
				$db->exec("DETACH comp;");
			}
		}
	}
	unset($result);	
	unset($astmt);
	unset($bstmt);
	if($foundcid) $filename = $cidfn;
}

$db=null;
if($db = new PDO('sqlite:'.DATA_DIR.$filename.'.db')) {
	$c=$db->query("SELECT cid FROM competition LIMIT 1");
	if($c->fetchColumn() == $cid) {
		$c->closeCursor();
		unset($c);
		$db->setAttribute(PDO::ATTR_TIMEOUT,25);  //set 25 second timeout on obtaining a lock
		$db->exec("PRAGMA foreign_keys = ON");
	} else {
		$db->exec("ATTACH 'football.ini' AS football");
		$c = $db->prepare("DELETE FROM football.cache WHERE cid = ?");
		$c->execute();
		$c->closeCursor();
		$db->exec("DETACH football");
		$db=false;
	}
}
?>
