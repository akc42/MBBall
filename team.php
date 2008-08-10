<?php
if (!defined('BALL'))
	die('Hacking attempt...');
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


$teams = array(array());
$sizes = array(array());

// It would be good to have a set of team names. 
$sql = 'SELECT *  FROM team_in_competition t JOIN team USING (tid)'; 
$sql .= ' WHERE t.cid = '.dbMakeSafe($cid).' ORDER BY confid,divid,tid;';
$result = dbQuery($sql);
if(dbNumRows($result) > 0 ) {
	while ($row=dbFetchRow($result)) {
		$pick = array();
		$pick['tid']=$row['tid'];
		$pick['name']=$row['name'];
		$pick['logo']=$row['logo'];
		$pick['url']=$row['url'];
		$pick['mp'] = ($row['made_playoff'] == 't');
		$teams[$row['confid']][$row['divid']][] = $pick;
		if (isset($sizes[$row['confid']][$row['divid']])) {
			$sizes[$row['confid']][$row['divid']]++;
		} else {
			$sizes[$row['confid']][$row['divid']] = 1;
		}
	}
}
dbFree($result);


