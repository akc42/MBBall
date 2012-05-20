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

define('DATA_DIR','/home/alan/dev/football/data/');
define('GOOGLE_ACCOUNT');


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
function tick() {
	echo '<img src="images/tick.gif" alt="Correct" />';
}

//Check that call is legit
if(!isset($_COOKIE['MBBall'])) forbidden();
$user = unserialize($_COOKIE['MBBall']);
if ($user['key'] != sha1(PRIVATE_KEY.$user['timestamp'].$user['uid'].serialize($user['data']))) forbidden();
$uid = $user['id']; //This is the one parameter that is important.

//This is a convenient place to force everything we output to not be cached 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

class DB extends PDO {
    private static $instance;
    private function __construct()
    {
    	parent::__construct('sqlite:'.DATA_DIR.'football.db');
    }

    public static function get()
    {
        if (!isset(self::$instance)) {
        	$className = __CLASS__;
        	if(!file_exists(DATA_DIR.'football.db')) {
				self::$instance = new $className;
				self::$instance->exec(file_get_contents(dirname(__FILE__).'/database.sql'));  //setup database
			} else {
				self::$instance = new $className;	
			}
			self::$instance->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);  // We might as well know about any problems asap (for debugging, drop for production)
			self::$instance->setAttribute(PDO::ATTR_TIMEOUT,25);  //set 25 second timeout on obtaining a lock
			self::$instance->exec("PRAGMA foreign_keys = ON");
        }
        return self::$instance;
    }
    public function prepare($sql) {
    	return new STMT(parent::prepare($sql));
    }
    public function getCounts() {
    	return STMT::getCount();
    }
}

class STMT {
	private static $count = 0;
	private $_s;
	public function __CONSTRUCT($s) {
		$this->_s = $s;
	}
	public function bindString($i,$string) {
		$this->_s->bindValue($i,$string);
	}
	public function bindInt($i,$value) {
		$this->_s->bindValue($i,$value,PDO::PARAM_INT);
	}
	public function exec() {
		self::$count++;
		$this->_s->execute();
	}
	public function fetchRow() {
		return $this->_s->fetch(PDO::FETCH_ASSOC);
	}
	public function fetchValue() {
		return $this->_s->fetchColumn();
	}
	public function close() {
		$this->_s->closeCursor();
	}
	public function __destruct() {
		$this->_s->closeCursor();
	}
	public static function getCount() {
		return self::$count;
	}
}

$db = DB::get();
