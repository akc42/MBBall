					<div id="emoticons">
<?php
/* Football Picking Competition
 *	Copyright (c) 2008 Alan Chandler
 *	See COPYING.txt in this directory for details of licence terms
*/

// Open a known directory, and proceed to read its contents
	$dir = '../static/images/emoticons';
	$fns = scandir($dir);
	foreach ($fns as $filename) {

		if(filetype($dir.'/'.$filename) == 'file') {
			$split = splitFIlename($filename);
			if($split[1] == 'gif') {
				echo '<img src="/static/images/emoticons/'.$filename.'" alt=":'.$split[0].'" title="'.$split[0].'" />';
				echo "\n";
			}
		}
	}
?>					</div>
