<?php
/*
 	Copyright (c) 2008-2012 Alan Chandler
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

define('GOOGLE_ACCOUNT','UA-6767400-1');  //Google account of the forum we are requesting details from (melindasbacksup.com)

define('SMF_FOOTBALL',		21);  //Group that can administer
define('SMF_BABY',		10);  //Baby backup
define('MBBALL_KEY','Football9Key7AID'); //Must match index.php of main app (and change for new installations)
define('MBBALL_CHECK','FOOTBILL'); //8 chars must match index.php of main app (and change for new installations)


/*
 * NOTE: This file can be used as a template for other similar applications.  We do not necessarily have to return
 * the same parameters nor use SMF to validate ourselves.  We are just doing that in the context of Melinda's Backups in this case
 */


function forbidden() {
	header('HTTP/1.0 403 Forbidden');
	?><html>
    <head>
        <style type="text/css">
            body {
                font-family: Arial;
                color: #345;
            }
            h1 {
                border-bottom: 3px solid #345;
            }
            a {
                color: #666;
            }
        </style>
    </head>
    <body>
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo GOOGLE_ACCOUNT;?>']);
  _gaq.push(['_trackPageview']);
</script>        
<h1>Forbidden</h1>
        <p>This URL is intended to only be called by authorised applications</p>
<!-- Google Analytics Tracking Code -->
  <script type="text/javascript">
    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
    })();
  </script>

    </body>
</html>
<?php
exit;
}



function simple_encrypt($text)
{
	return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256,
			MBBALL_KEY, $text, MCRYPT_MODE_ECB,
			mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256,
					MCRYPT_MODE_ECB), MCRYPT_RAND))));
}

function simple_decrypt($text)
{
	return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, MBBALL_KEY,
			base64_decode($text), MCRYPT_MODE_ECB,
			mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256,
					MCRYPT_MODE_ECB), MCRYPT_RAND)));
}


if(!isset($_GET['name'])) forbidden();
/*
 * In order to protect against malicious attempts to get user data, we require a hidden key
 */
if(substr(simple_decrypt($_GET['name']),0,8) != MBBALL_CHECK ) forbidden();

require_once($_SERVER['DOCUMENT_ROOT'].'/forum/SSI.php');

$user = Array();
$user_data = Array();

$groups =& $user_info['groups'];
if(isset($user_info['id'])) { //check if this is SMFv2
	$user['uid'] =& $user_info['id'];
} else {
	$user['uid'] = $ID_MEMBER;
}
$user['name'] =& $user_info['name'];
$user['email'] =& $user_info['email'];
$user['admin'] = true; //special setting for everyone to be an admin
$user['guest'] = false;
/*
 * The following is javascript run in the context of the index.php page that tried to load us
 * as a result we are setting a cookie in that context ...
 * NOTE: we are assuming mootools is loaded too
 */

echo "var Cookiedata = '".simple_encrypt(serialize($user))."';\n";  //ecrypted serialised version of the user data
echo "Cookie.write('MBBall',Cookiedata);\n"; //Write the cookie
//If not logged in to the forum, not allowed any further so redirect to page to say so
if($user_info['is_guest']) {
	/*
	 * The user is a guest, so we want to direct him to the url that tells him to sign into the forum
	 * We need to set the cookie so that we authorise use of the page.  Football.php will remove the cookie again.
 	 */
	echo "window.location = 'football.php';\n";
} else {
	/*
	 * ... and then reloading the page no that it 
 	 * has the cookie and therefore continues loading the page rather than coming back here
	 * 
	 */
	echo "window.location.reload();\n"; //And reload the page - which now the cookie is set should progress normally
}
unset($user_info);
unset($groups);

?>
