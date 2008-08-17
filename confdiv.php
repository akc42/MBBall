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



