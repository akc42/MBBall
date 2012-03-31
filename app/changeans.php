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
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['opid']) && isset($_GET['label']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

require_once('./db.inc');
$cid=$_GET['cid'];
$rid=$_GET['rid'];
$opid=$_GET['opid'];

dbQuery('BEGIN ;');
$result=dbQuery('SELECT * FROM option WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND opid = '.dbMakeSafe($opid).';');
if (dbNumRows($result) != 0) {
	dbQuery('UPDATE OPTION SET label = '.dbPostSafe($_GET['label']).' WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND opid = '.dbMakeSafe($opid).';');
	dbQuery('COMMIT ;');
	echo '{"cid":'.$cid.',"rid":'.$rid.',"opid":'.$opid.',"label":"'.$_GET['label'].'"}';
} else {
?><p>Option does not exist</p>
<?php
  dbQuery('ROLLBACK ;');
}
dbFree($result);
?>

