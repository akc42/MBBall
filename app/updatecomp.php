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
if(!(isset($_POST['cid']) && isset($_POST['desc']) && isset($_POST['adm']) && isset($_POST['condition']) 
		&& isset($_POST['playoffdeadline']) && isset($_POST['gap']) )) forbidden();

$db->exec("BEGIN TRANSACTION");

$u = $db->prepare("UPDATE participant SET admin_experience = 1 WHERE uid = ? ");
$u->bindInt(1,$_POST['adm']);
$u->exec();
unset($u);

$sql = "UPDATE competition SET description = ?, administrator = ?, condition = ? ";
$sql .= ", pp_deadline = ? ,gap = ? , open = ?, guest_approval = ?, results_cache = NULL WHERE cid = ?";
$c = $db->prepare($sql);
$c->bindString(1,$_POST['desc']);
$c->bindInt(2,$_POST['adm']);
$c->bindString(3,$_POST['condition']);
$c->bindInt(4,$_POST['playoffdeadline']);
$c->bindInt(5,$_POST['gap']*60);
$c->bindInt(6,isset($_POST['open'])?1:0);
$c->bindInt(7,isset($_POST['bbapproval'])?1:0);
$c->bindInt(8,$_POST['cid']);
$c->exec();
unset($c);
$db->exec("COMMIT");
echo '{"cid":'.$_POST['cid'].'}';
?>
