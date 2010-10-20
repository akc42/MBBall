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
require_once('./inc/confdiv.inc');

$teams = array(array());
$sizes = array(array());
foreach($confs as $confid => $conference) {
	foreach($divs as $divid => $division) {
		$sizes[$confid][$divid] = 0;
	}
} 
// It would be good to have a set of team names. 
foreach ($db->query("SELECT *  FROM team WHERE in_competition = 1 ORDER BY confid,divid,tid;") as $row) {
	$row['tid'] = trim($row['tid']);
	$pick = array();
	$pick['tid']=$row['tid'];
	$pick['name']=$row['name'];
	$pick['logo']=$row['logo'];
	$pick['url']=$row['url'];
	$pick['mp'] = ($row['made_playoff'] == 1);
	$teams[$row['confid']][$row['divid']][] = $pick;
	$sizes[$row['confid']][$row['divid']]++;
}
?>
