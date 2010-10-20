<?php
/*
 	Copyright (c) 2008,2009 Alan Chandler
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
if(!(isset($_POST['uid']) && isset($_POST['pass'])  && isset($_POST['cid']) && isset($_POST['desc']) && isset($_POST['adm'])
	&& isset($_POST['playoffdeadline']) && isset($_POST['gap']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['uid'];
$password = $_POST['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$adm=dbMakeSafe($_POST['adm']);
dbQuery('BEGIN ;');
$result=dbQuery('SELECT * FROM participant WHERE uid = '.$adm.';');
if (dbNumRows($result) == 0) {
	$adm = 'NULL';
} else {
	dbQuery('UPDATE participant SET admin_experience = TRUE WHERE uid = '.$adm.';');
}
dbFree($result);

$sql = 'UPDATE competition SET description = '.dbPostSafe($_POST['desc']).', administrator = '.$adm.', condition = '.dbPostSafe($_POST['condition']);
$sql .= ', pp_deadline = '.dbMakeSafe($_POST['playoffdeadline']).',gap = '.dbMakeSafe($_POST['gap']*60); 
if (isset($_POST['open'])) {
	$sql .= ', open = TRUE';
} else {
	$sql .= ', open = FALSE';
}
if (isset($_POST['bbapproval'])) {
	$sql .= ', bb_approval = TRUE';
} else {
	$sql .= ', bb_approval = FALSE';
}

$sql .= ' WHERE cid = '.dbMakeSafe($_POST['cid']).' ;';
dbQuery($sql);
dbQuery('COMMIT ;');
echo '{"cid":'.$_POST['cid'].'}';
?>
