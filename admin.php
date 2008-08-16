<?php
if(!(isset($_GET['uid']) && isset($_GET['pass']) && isset($_GET['v'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($_GET['pass'] != sha1("Football".$uid))
	die('Hacking attempt got: '.$_GET['pass'].' expected: '.sha1("Football".$uid));
if (!isset($_GET['global']) && !isset($_GET['cid']))
	die('Hacking attempt - need cid or global');
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Melinda's Backups Football Pool Administration</title>
	<link rel="stylesheet" type="text/css" href="ball.css" title="mbstyle"/>
	<!--[if lt IE 7]>
		<link rel="stylesheet" type="text/css" href="ball-ie.css"/>
	<![endif]-->
	<script src="/static/scripts/mootools-1.2-core-nc.js" type="text/javascript" charset="UTF-8"></script>
	<script src="/static/scripts/mootools-1.2-more-nc.js" type="text/javascript" charset="UTF-8"></script>
	<script src="mbball.js" type="text/javascript" charset="UTF-8"></script>
</head>
<body>
<script type="text/javascript">
	<!--

var MBBmgr;
window.addEvent('domready', function() {
	MBBmgr = new MBBAdmin('<?php echo $_GET['v'];?>',{uid: '<?php echo $uid;?>', 
				password : '<?php echo $password; ?>',
				admin :<?php if(isset($_GET['global'])) {echo 'true';} else {echo 'false';}?>},
				<?php if(isset($_GET['cid'])) {echo $_GET['cid'] ;}else{ echo '0';}?>,
                                $('errormessage')
                             );
});	

	// -->
</script>

<table id="header" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" >
<tbody>
	<tr>
	<td align="left" width="30" class="topbg_l" height="70">&nbsp;</td>
	<td align="left" colspan="2" class="topbg_r" valign="top"><a href="/" alt="Main Site Home Page">
		<img  style="margin-top: 24px;" src="/static/images/mb-logo-community.gif" alt="Melinda's Backups Community" border="0" /></a>	
		</td>
	<td align="right" width="400" class="topbg" valign="top">
	<span style="font-family: tahoma, sans-serif; margin-left: 5px;">Melinda's Backups Community</span>
	</td>
		<td align="right" width="25" class="topbg_r2" valign="top">
		<div id="competitionNameContainer">
			<h1>Administration Page</h1>
		</div>
		<!-- blank -->
		</td>
	</tr>  </tbody>
</table>
<ul id="menu">
	<li><a href="/forum"><span>Return to the Forum</span></a></li>
	<li><a href="/football"><span>Return the User Page</span></a></li>
</ul>
<div id="content">
<div id="errormessage"></div>
<table class="layout">
	<tbody>
		<tr><td colspan="3"><div id="competitions"></div></td></tr>
		<tr><td colspan="2"><div id="competition"></div></td><td rowspan="3"><div id="rounds"></div></td></tr>
		<tr><td colspan="2"><div id="newround"></div></td></tr>
		<tr><td><div id="round"></div></td><td id="options"></td></tr>
		<tr><td colspan="2" id="matches"></td><td><div id="teams"></div></td></tr>
		<tr><td colspan="3"><div id="registered"></div></td></tr>
	</tbody>
</table>

<div id="copyright"><hr/>MBball <span id="version"></span> &copy; 2008 Alan Chandler.  Licenced under the GPL</div>
</div>
</body>

</html>