<?php
if(!(isset($_GET['user']) && isset($_GET['password']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['user'];
if ($_GET['password'] != sha1("Key".$uid))
	die('Hacking attempt got: '.$_GET['password'].' expected: '.sha1("Key".$uid));

$txt = 'MBball version: '.$_GET['mbchat'].', Mootools Version : '.$_GET['version'].' build '.$_GET['build'] ;
$txt .=' Browser : '.$_GET['browser'].' on Platform : '.$_GET['platform'];
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
// Do something here maybe
echo '{"Logout" : '.$txt.'}' ;
?> 
