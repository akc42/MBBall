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
if(!(isset($_GET['uid']) && isset($_GET['pass']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
if ($_GET['pass'] != sha1("Football".$uid))
	die('Hacking attempt got: '.$_GET['password'].' expected: '.sha1("Key".$uid));

$txt = 'MBball version: '.$_GET['mbchat'].', Mootools Version : '.$_GET['version'].' build '.$_GET['build'] ;
$txt .=' Browser : '.$_GET['browser'].' on Platform : '.$_GET['platform'];
require_once('./db.inc');
// Do something here maybe
echo '{"Logout" : '.$txt.'}' ;
?> 
