<?php
/*
 	Copyright (c) 2008,2009,2010 Alan Chandler
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
    along with MBBall (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.

*/
if(!(isset($_GET['uid']) && isset($_GET['pass']) ))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['uid'];
$password = $_GET['pass'];
if ($_GET['pass'] != sha1("Football".$uid))
	die('Hacking attempt got: '.$_GET['pass'].' expected: '.sha1("Football".$uid));
if (!isset($_GET['global']) && !isset($_GET['cid']))
	die('Hacking attempt - need cid or global');
define ('BALL',1);   //defined so we can control access to some of the files.
require_once('db.php');


?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Melinda's Backups Football Pool Administration</title>
	<link rel="stylesheet" type="text/css" href="/static/scripts/calendar/calendar.css"/>
	<link rel="stylesheet" type="text/css" href="ball.css"/>
	<!--[if lt IE 7]>
		<link rel="stylesheet" type="text/css" href="ball-ie.css"/>
	<![endif]-->
	<script src="mootools-1.2.4-core-yc.js" type="text/javascript" charset="UTF-8"></script>
	<script src="mootools-1.2.4.1-drag_move.js" type="text/javascript" charset="UTF-8"></script>
	<script src="calendar/calendar.js" type="text/javascript" charset="UTF-8"></script>
	<script src="mbball.js" type="text/javascript" charset="UTF-8"></script>
</head>
<body>


<script type="text/javascript">
	<!--

var MBBmgr;
window.addEvent('domready', function() {
	MBBmgr = new MBBAdmin({uid: '<?php echo $uid;?>',
				password : '<?php echo $password; ?>',
				admin :<?php if(isset($_GET['global'])) {echo 'true';} else {echo 'false';}?>},
				<?php if(isset($_GET['cid'])) {echo $_GET['cid'] ;}else{ echo '0';}?>,
                                $('errormessage')
                             );
});	

	// -->
</script>

<div id="header"><div class="frame">
	<div id="top_section">
		<h1 class="logo">
			<!-- replace this section with your own logo -->
			<a href="/forum/index.php">
				<img src="/static/images/mb-logo-community.gif" alt="Melinda's Backups Community" border="0" />
			</a>
		</h1>
		<!-- Only for mb.com -->
		<div id="md_banner">
		    <a href="http://melindadoolittle.com" alt="Main Site Home Page" style="text-decoration:none;margin-top:5px;">
                <span >MelindaDoolittle.com</span><br/>
                <img style="margin-top:5px;" src="/static/images/small_banners/rotate.php" alt="Melinda Doolittle" border="0" />
            </a>
        </div>
		<div id="siteslogan">Melinda's Backups Community</div>
	</div>
	<div id="competitionNameContainer">
		<h1>Administration Page</h1>
	</div>
	<ul id="menu">
		<li><a href="/forum"><span>Return to the Forum</span></a></li>
		<li><a href="/football"><span>Return the User Page</span></a></li>
	</ul>
</div></div> <!-- #header .frame -->
<div id="wrapper"><div class="frame">
<div id="content">
<div id="errormessage"></div>
<table class="layout">
	<tbody>
		<tr><td colspan="3"><div id="competitions"></div></td></tr>
		<tr><td colspan="2"><div id="competition"></div></td><td rowspan="3"><div id="rounds"></div></td></tr>
		<tr><td colspan="2"><div id="newround"></div></td></tr>
		<tr><td><div id="round"></div></td><td id="options"></td></tr>
		<tr><td colspan="3"><?php require_once('emoticons.php');?></td>
		<tr><td colspan="2" id="matches"></td><td><div id="teams"></div></td></tr>
		<tr><td colspan="3"><div id="registered"></div></td></tr>
		<tr><td colspan="3"><div id="userpick"></div></td></tr>
	</tbody>
</table>

</div>
</div></div> <!-- #wrapper .frame -->
<div id="footer"><div class="frame">
	<div id="copyright">MBball <span><?php include('version.php');?></span> &copy; 2008-2011 Alan Chandler.  Licenced under the GPL</div>
</div></div><!-- #footer .frame -->
</body>

</html>
