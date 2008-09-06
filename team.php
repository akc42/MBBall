<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/
if (!defined('BALL'))
	die('Hacking attempt...');
require_once('confdiv.php');

$teams = array(array());
$sizes = array(array());
foreach($confs as $confid => $conference) {
	foreach($divs as $divid => $division) {
		$sizes[$confid][$divid] = 0;
	}
} 
// It would be good to have a set of team names. 
$sql = 'SELECT *  FROM team_in_competition t JOIN team USING (tid)'; 
$sql .= ' WHERE t.cid = '.dbMakeSafe($cid).' ORDER BY confid,divid,tid;';
$result = dbQuery($sql);
if(dbNumRows($result) > 0 ) {
	while ($row=dbFetchRow($result)) {
		$row['tid'] = trim($row['tid']);
		$pick = array();
		$pick['tid']=$row['tid'];
		$pick['name']=$row['name'];
		$pick['logo']=$row['logo'];
		$pick['url']=$row['url'];
		$pick['mp'] = ($row['made_playoff'] == 't');
		$teams[$row['confid']][$row['divid']][] = $pick;
		$sizes[$row['confid']][$row['divid']]++;
	}
}
dbFree($result);
?>