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
//Lets get the conference and div lists as this is data useful throughout this page
$confs = array();
$divs = array(); 
$result = dbQuery('SELECT * FROM conference ORDER BY confid;');
while($row = dbFetchRow($result)) {
	$confs[$row['confid']] = $row['name'];
}
dbFree($result);
$result = dbQuery('SELECT * FROM division ORDER BY divid;');
while($row = dbFetchRow($result)) {
	$divs[$row['divid']] = $row['name'];
}
dbFree($result);
?>



