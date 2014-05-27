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

define('MBBALL_JSON_FILE','./auth.json');

define('MBBALL_KEY','Football9Key7AID'); //Must match index.php of main app (and change for new installations)
define('MBBALL_CHECK','FOOTBILL'); //8 chars must match index.php of main app (and change for new installations)


/*
 * NOTE: This file can be used as a template for other similar applications.  We do not necessarily have to return
 * the same parameters nor use SMF to validate ourselves.  We are just doing that in the context of Melinda's Backups in this case
 */


function forbidden() {
	http_response_code(403);
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

$user = json_decode(file_get_contents(MBBALL_JSON_FILE),true);
if(is_null($user)) forbidden();
if($user['guest']) {
	/*
	 * The user is a guest, so we want to direct him to the url that tells him to sign into the forum
 	 */
	echo "window.location = '/football.php';\n";

} else {
/*
 * The following is javascript run in the context of the index.php page that tried to load us
 * as a result we are setting a cookie in that context ...
 * NOTE: we are assuming mootools is loaded too
 */

    echo "var Cookiedata = '".simple_encrypt(serialize($user))."';\n";  //ecrypted serialised version of the user data
    echo "Cookie.write('MBBall',Cookiedata);\n"; //Write the cookie
	/*
	 * ... and then reloading the page no that it 
 	 * has the cookie and therefore continues loading the page rather than coming back here
	 * 
	 */
	echo "window.location.reload();\n"; //And reload the page - which now the cookie is set should progress normally
}
unset($user);
?>
