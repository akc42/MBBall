<?php
if(!(isset($_GET['uid']) && isset($_GET['pass'])  && isset($_GET['cid']) && isset($_GET['rid']) && isset($_GET['hid']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($password != sha1("Football".$uid))
	die('Hacking attempt got: '.$password.' expected: '.sha1("Football".$uid));

define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');
$cid=$_GET['cid'];
$rid=$_GET['rid'];
$hid=$_GET['hid'];

dbQuery('BEGIN ;');
$result=dbQuery('SELECT * FROM match WHERE cid = '.dbMakeSafe($cid).' AND rid = '.dbMakeSafe($rid).' AND hid = '.dbMakeSafe($hid).';');
if (dbNumRows($result) == 0) {
  dbQuery('INSERT INTO match(cid, rid, hid) VALUES ('.dbMakeSafe($cid).','.dbMakeSafe($rid).','.dbMakeSafe($hid).');');
  dbQuery('COMMIT ;');
  echo '{"items":[{"name":"form","type":"form","params":{"action":"#"},"parent":"root"},
                       {"name":"cid","type":"input","params":{"type":"hidden","name":"cid","value":'.$cid.'},"parent":"form"},
                       {"name":"rid","type":"input","params":{"type":"hidden","name":"rid","value":'.$rid.'},"parent":"form"},
                       {"name":"ihid","type":"input","params":{"type":"hidden","name":"hid","value":'.$hid.'},"parent":"form"},
                       {"name":"iaid","type":"input","params":{"type":"hidden","name":"aid"},"parent":"form"},
                       {"name":"dhid","type":"div","params":{"class":"hid"},"parent":"form"},
                       {"name":"shid","type":"span","params":{"text":"'.$hid.'"},"parent":"dhid"},
                       {"name":"daid","type":"div","params":{"class":"aid"},"parent":"form"},
                       {"name":"said","type":"span","params":{"text":"---"},"parent":"daid"},
                       {"name":"lopen","type":"label","params":{},"parent":"form"},
                       {"name":"iopen","type":"input","params":{"type":"checkbox","name":"open"},"parent":"lopen"},
                       {"name":"sopen","type":"span","params":{"text":"Open"},"parent":"lopen"},
                       {"name":"ddel","type":"div","params":{"class":"del"},"parent":"form"},
                       {"name":"dhs","type":"div","params":{"class":"hscore"},"parent":"form"},
                       {"name":"ihs","type":"input","params":{"type":"text",},"parent":"dhs"},
                       {"name":"das","type":"div","params":{"class":"ascore"},"parent":"form"},
                       {"name":"ias","type":"input","params":{"type":"text",},"parent":"das"},
                       {"name":"dcs","type":"div","params":{"class":"cscore"},"parent":"form"},
                       {"name":"ics","type":"input","params":{"type":"text",},"parent":"dcs"},
                       {"name":"dmt","type":"div","params":{"class":"mtime"},"parent":"form"},
                       {"name":"imt","type":"input","params":{"type":"text",},"parent":"dmt"},
                       {"name":"dcmt","type":"div","params":{"class":"comment"},"parent":"form"},
                       {"name":"tcmt","type":"textarea","params":{},"parent":"dcmt"},
                       {"name":"clear","type":"div","params":{"class":"clear"},"parent":"root"},
                      ]}';
} else {
  echo '<p>Match alread exists</p>';
	dbQuery('ROLLBACK ;');
}
dbFree($result);

