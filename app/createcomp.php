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


if(!(isset($_POST['desc']) && isset($_POST['adm']))) forbidden();

$adm=$_POST['adm'];
$db->exec("BEGIN TRANSACTION");

$u = $db->prepare("SELECT COUNT(*) FROM participant WHERE uid = ? ");
$u->bindInt(1,$adm);
$users = $u->fetchValue();
unset($u);

$u = $db->prepare("UPDATE participant SET admin_experience = 1 WHERE uid = ?");
$u->bindInt(1,$adm);
$u->exec();
unset($u);

$c= $db->prepare("INSERT INTO competition(description,administrator) VALUES(?,?)");
$c->bindString(1,$_POST['desc']);
$c->bindInt(2,$adm);
$c->exec();
unset($c);

$c = $db->prepare("SELECT last_insert_rowid()");
$lastval = $c->fetchValue();
unset($c);

$c = $db->prepare("SELECT COUNT(*) FROM competition");
$noComps =  $c->fetchValue();
unset($c);

if(isset($_POST['setdefault']) || $noComps <= 1) {
	$d = $db->prepare("UPDATE settings SET value = ? WHERE name = 'default_competition'");
	$d->bindInt(1,$lastval);
	$d->exec();
	unset($d);
}
$db->exec("COMMIT");

echo '{"cid":'.$lastval.'}';
?>
