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
if(!(isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['gap']) && isset($_POST['ppd']) )) forbidden();

$cid=$_POST['cid'];
$rid=$_POST['rid'];

$gap = $_POST['gap'];
$ppd = $_POST['ppd'];

$db->exec("BEGIN TRANSACTION");


if (isset($_POST['bqdeadline']) && $_POST['bqdeadline'] > time()) {
	$p = $db->prepare("DELETE FROM option_pick WHERE uid = ? AND cid = ? AND rid = ?");
	$p->bindInt(1,$uid);
	$p->bindInt(2,$cid);
	$p->bindInt(3,$rid);
	$p->exec();
	unset($p);
	$p = $db->prepare("INSERT INTO option_pick(uid,cid,rid,opid,comment) VALUES (?,?,?,?,?)");
	$p->bindInt(1,$uid);
	$p->bindInt(2,$cid);
	$p->bindInt(3,$rid);
	if (isset($_POST['opid']) && $_POST['opid'] != '') {
		$p->bindInt(4,$_POST['opid']);
		if (isset($_POST['Cbonus']) && $_POST['Cbonus'] != '') {
			$p>bindString(5,$_POST['Cbonus']);
		} else {
			$p->bindNull(5);
		}
		$p->exec();
	} else {
		if (isset($_POST['Cbonus']) && $_POST['Cbonus'] != '') {
			$p->bindNull(4);
			$p>bindString(5,$_POST['Cbonus']);
			$p->exec();
		}
	}
	unset($p);
}

if ($rid != 0) { //only expecting picks from a round if there is one.
	$m = $db->prepare("SELECT aid FROM match WHERE open = 1 AND match_time > ? AND cid = ? AND rid = ? ");
	$m->bindInt(1,time()+$gap);
	$m->bindInt(2,$cid);
	$m->bindInt(3,$rid);
	$p = $db->prepare("INSERT OR REPLACE INTO pick(uid,cid,rid,aid,pid,over_selected,comment) VALUES (?,?,?,?,?,?,?)");
	$p->bindInt(1,$uid);
	$p->bindInt(2,$cid);
	$p->bindInt(3,$rid);
    while($row = $m->FetchRow()) {
	    $aid = trim($row['aid']);
	    $p->bindString(4,$aid);
	    if(isset($_POST['M'.$aid])) {
		    $p->bindString(5,$_POST['M'.$aid]);
	    } else {
		    $p->bindNull(5);
	    }
	    if(isset($_POST['O'.$aid])) {
		    $p->bindInt(6,$_POST['O'.$aid] == 'O'?1:0);
	    } else {
		    $p->bindNull(6);
	    }
	    if(isset($_POST['C'.$aid]) && $_POST['C'.$aid] != '') {
		    $p->bindString(7,$_POST['C'.$aid]);
	    } else {
		    $p->bindNull(7);
	    }
	    $p->exec();
    }
    unset($p);
    unset($m);
}
if ($ppd != 0 && $ppd > time()) {

	require_once('./inc/confdiv.inc');
	$p = $db->prepare("DELETE FROM wildcard_pick WHERE uid = ? AND cid = ? ");
	$p->bindInt(1,$uid);
	$p->bindInt(2,$cid);
	$p->exec();
	unset($p);
	$p = $db->prepare("DELETE FROM div_winner_pick WHERE uid = ? AND cid = ? ");
	$p->bindInt(1,$uid);
	$p->bindInt(2,$cid);
	$p->exec();
	unset($p);
	
	$w = $db->prepare("INSERT INTO wildcard_pick(uid,cid,confid,opid,tid) VALUES (?,?,?,?,?)");
	$w->bindInt(1,$uid);
	$w->bindInt(2,$cid);
	$d = $db->prepare("INSERT INTO div_winner_pick(uid,cid,confid,divid,tid) VALUES (?,?,?,?,?)");
	$d->bindInt(1,$uid);
	$d->bindInt(2,$cid);
	
	
	foreach($confs as $confid => $conference) {
		$w->bindString(3,$confid);
		$d->bindString(3,$confid);		
		if(isset($_POST['W1'.$confid])) {
			$w->bindInt(4,1);
			$w->bindString(5,$_POST['W1'.$confid]);
			$w->exec();
			$w->close();
		}
		if(isset($_POST['W2'.$confid])) {
			$w->bindInt(4,2);
			$w->bindString(5,$_POST['W2'.$confid]);
			$w->exec();
			$w->close();
		}
		foreach($divs as $divid => $division) {
			$d->bindString(4,$divid);
			if (isset($_POST['D'.$divid.$confid])) {
				$d->bindString(5,$_POST['D'.$divid.$confid]);
				$d->exec();
				$d->close();
			}
		}
	}
	unset($w);
	unset($d);
}

$db->exec("COMMIT");

echo '{"uid":'.$uid.',"cid":'.$cid.',"rid":'.$rid.'}';
?>
