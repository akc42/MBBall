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
if(!(isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['auid']) && isset($_GET['gap']) && isset($_GET['pod']) && isset($_GET['name']) ))
	forbidden();

$cid = $_GET['cid'];
$rid = $_GET['rid'];
$gap = $_GET['gap'];
$playoff_deadline = $_GET['pod'];
$userPicks = $_GET['name'];
$uid = $_GET['auid'];
if($rid != 0 && $cid !=0) {
	$r =$db->prepare("SELECT * FROM round WHERE open = 1 AND cid = ? AND rid = ?");
	$r->bindInt(1,$cid);
	$r->bindInt(2,$rid);
	if($rounddata = $r->fetchRow()) require('./inc/userpick.inc');
	unset($rounddata);
}
?>
