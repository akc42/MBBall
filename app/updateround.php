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
if(!(isset($_POST['cid']) && isset($_POST['rid']) && isset($_POST['rname'])
	&& isset($_POST['deadline']) && isset($_POST['value']) )) forbidden();

$sql = "UPDATE round SET name = ?, value = ?, deadline = ?, open = ?, ou_round = ?, valid_question = ?,answer = ?, ";
$sql .= "question = ? WHERE cid = ? AND rid = ?";
$r = $db->prepare($sql);
$r->bindString(1,$_POST['rname']);
$r->bindInt(2,$_POST['value']);
$r->bindInt(3,$_POST['deadline']);
$r->bindInt(4,isset($_POST['open'])?1:0);
$r->bindInt(5,isset($_POST['ou'])?1:0);
if (isset($_POST['validquestion'])) {
	$r->bindInt(7,1);
	if(isset($_POST['answer'])) {
		$r->bindInt(8,$_POST['answer']);
	} else {
		$r->bindNull(8);
	}
} else {
	$r->bindInt(7,0);
	$r->bindNull(8);
}
$r->bindString(9,isset($_POST['question'])?$_POST['question']:'');
$r->bindInt(10,$cid);
$r->bindInt(11,$rid);
$r->exec();
unset($r);

echo '{"cid":'.$_POST['cid'].',"rid":'.$_POST['rid'].'}';
?>
