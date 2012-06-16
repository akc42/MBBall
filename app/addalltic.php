<?php
/*
 	Copyright (c) 2008-2012 Alan Chandler
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
require_once('./inc/db.inc');
if(!(isset($_GET['cid']))) forbidden();

$cid=$_GET['cid'];

//This should give us all the teams
$i = $db->prepare("INSERT INTO team_in_competition(cid,tid) SELECT ? AS cid, t.tid FROM team t LEFT JOIN team_in_competition c ON t.tid = c.tid AND cid = ? WHERE c.tid IS NULL ");
$i->bindInt(1,$cid);
$i->bindInt(2,$cid);

$t = $db->prepare("SELECT tid FROM team_in_competition WHERE cid = ? ORDER BY tid");
$t->bindInt(1,$cid);

$db->exec('BEGIN TRANSACTION');

$i->exec();
unset($i);
//Now return all the teams in the competition


echo '{"teams":[';
if($row=$t->FetchRow()) {
	echo '"'.$row['tid'].'"';
	while($row = $t->FetchRow()) {
		echo ',"'.$row['tid'].'"';
	}
}
unset($t);
$db->exec("COMMIT");

echo ']}';
?>
		
