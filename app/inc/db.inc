<?php
/*
 	Copyright (c) 2010 Alan Chandler
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
if(!defined('DATA_DIR')) define('DATA_DIR','/home/alan/football/db/');
if(!defined('PRIVATE_KEY')) define('PRIVATE_KEY','Football19Key');



//This is a convenient place to force everything we output to not be cached (even 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// $cid must be set before calling db.inc


if(!$db = new PDO('sqlite:'.DATA_DIR.$cid.'.db')) die ('Failed to open database');
$db->setAttribute(PDO::ATTR_TIMEOUT,25);  //set 25 second timeout on obtaining a lock
$db->exec("PRAGMA foreign_keys = ON");

?>
