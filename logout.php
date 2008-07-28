<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
if ($_GET['pass'] != sha1("Football".$uid))
	die('Hacking attempt got: '.$_GET['password'].' expected: '.sha1("Key".$uid));

$txt = 'MBball version: '.$_GET['mbchat'].', Mootools Version : '.$_GET['version'].' build '.$_GET['build'] ;
$txt .=' Browser : '.$_GET['browser'].' on Platform : '.$_GET['platform'];
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
// Do something here maybe
echo '{"Logout" : '.$txt.'}' ;
?> 
