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
if(!(isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['opid']) )) forbidden();

$cid=$_GET['cid'];
$rid=$_GET['rid'];
$opid=$_GET['opid'];

$db->exec("BEGIN TRANSACTION");

//Are there are any options for this round
$o = $db->prepare("SELECT COUNT(*) FROM option WHERE cid = ? AND rid = ?"); 
$o->bindInt(1,$cid);
$o->bindInt(2,$rid);
$noOpts = $o->fetchValue();
unset($o);
$o = $db->prepare("SELECT COUNT(*) FROM option WHERE cid = ? AND rid = ? AND opid = ? ");
$o->bindInt(1,$cid);
$o->bindInt(2,$rid);
$o->bindInt(3,$opid);
$currentOpt = $o->fetchValue();
unset($o);

if ($currentOpt == 0) {
	$o = $db->prepare("INSERT INTO option (cid,rid,opid) VALUES (?,?,?)");
	$o->bindInt(1,$cid);
	$o->bindInt(2,$rid);
	$o->bindInt(3,$opid);
	$o->exec();
	unset($o);
	if ($noOpts == 0) {
	  	//This is the first option created for this round
	  	$r = $db->prepare("UPDATE round SET answer = 0 WHERE cid = ? AND rid = ? ");
		$r->bindInt(1,$cid);
		$r->bindInt(2,$rid);
		$r->exec();
		unset($r);
	}
	$db->exec("COMMIT");	
	echo '{"cid":'.$cid.',"rid":'.$rid.',"opid":'.$opid.'}';
} else {
?><p>Option already exists</p>
<?php
	$db->exec("ROLLBACK");
}
?>

