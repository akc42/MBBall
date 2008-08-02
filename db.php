<?php
//This is a convenient place to force everything we output to not be cached (even 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	if (!defined('BALL'))
		die('Hacking attempt...');
	$db_server = 'localhost';
	$db_name = 'melindas_ball';
	$db_user = 'melindas_ball';
	$db_password = 'xxxxxx';
	pg_connect("host=$db_server dbname=$db_name user=$db_user
		password=$db_password") 
			or die('Could not connect to database: ' . pg_last_error());
	function dbQuery($sql) {
		$result = pg_query($sql);
		if (!$result) {
			echo $sql;
			echo "\n\n";
			die('database query failed: '.pg_last_error());
		}
		return $result;
	}
	function dbMakeSafe($value) {
		return "'".pg_escape_string($value)."'" ;
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
?>
