<?php
//This is a convenient place to force everything we output to not be cached (even 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	if (!defined('BALL'))
		die('Hacking attempt...');
//  These are the patterns that we will use to search for some simple bbcode sequences
	$search[0]='/\[b\](.*?)\[\/b\]/';
	$search[1]='/\[s\](.*?)\[\/s\]/';
	$replace[0]='<b>$1</b>';
	$replace[1]='<del>$1</del>';

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
		return $result;
	}
	function dbMakeSafe($value) {
		return "'".pg_escape_string($value)."'" ;
	}
	function dbPostSafe($text) {
	  if ($text == '') return 'NULL';
	  return dbMakeSafe(htmlentities($text,ENT_QUOTES,'UTF-8',false));
	}
	function dbBBcode($text) {
		global $search,$replace;
		return preg_replace($search,$replace,$text);
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
