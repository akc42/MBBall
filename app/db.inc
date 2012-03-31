<?php
/*
 	Copyright (c) 2008,-2011 Alan Chandler
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
    along with MBBall (file supporting/COPYING.txt).  If not, 
    see <http://www.gnu.org/licenses/>.

*/

//This is a convenient place to force everything we output to not be cached (even 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

$querycounter = 0;

$db_server = 'localhost';
$db_name = 'melindas_ball';
$db_user = 'melindas_ball';
$db_password = 'xxxxxx';
pg_connect("host=$db_server dbname=$db_name user=$db_user
	password=$db_password") 
		or die('Could not connect to database: ' . pg_last_error());
function dbQuery($sql) {
	global $querycounter;
	$result = pg_query($sql);
	if (!$result) {
		echo '<tt>';
		echo "<br/><br/>\n";
		print_r(debug_backtrace());			
		echo "<br/><br/>\n";
		echo $sql;
		echo "<br/><br/>\n\n";
		echo pg_last_error();
		echo "<br/><br/>\n\n";
		echo '</tt>';
		die('<p>Please inform <i>webmaster@melindasbackups.com</i> that a database query failed in the football and include the above text.<br/><br/>Thank You</p>');
	}
	$querycounter++;
	return $result;
}
function dbMakeSafe($value) {
	if (!get_magic_quotes_gpc()) {
		$value=pg_escape_string($value);
	}
	return "'".$value."'" ;
}
function dbPostSafe($text) {
  if ($text == '') return 'NULL';
  return dbMakeSafe(htmlentities($text,ENT_QUOTES,'UTF-8',false));
}
function dbNumRows($result) {
	return pg_num_rows($result);
}
function dbFetchRow($result) {
	return pg_fetch_assoc($result);
}
function dbFree($result){
	pg_free_result($result);
}
function dbRestartQuery($result) {
	pg_result_seek($result,0);
}
function dbFetch($result) {
	return pg_fetch_all($result);
}
function tick() {
	echo '<img src="images/tick.gif" alt="Correct" />';
}
?>
