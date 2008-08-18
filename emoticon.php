<?php
/* Emoticon loading software
	Copyright (c) 2008 Alan Chandler
	see COPYING.txt in this directory for more details
*/
// Path to the Ball directory:

function splitFilename($filename) {
	$pos = strrpos($filename, '.');
	if ($pos === false) { // dot is not found in the filename
		return array($filename, ''); // no extension
	} else {
		$basename = substr($filename, 0, $pos);
		$extension = substr($filename, $pos+1);
		return array($basename, $extension);
	} 
}

// Open a known directory, and proceed to read its contents

if ($dh = opendir($dir)) {
	$doneFirst = false;
	echo '[';
	while (($filename = readdir($dh)) !== false) {
		if(filetype($dir.$filename) == 'file') {
			$split = splitFIlename($filename);
			if($split[1] == 'gif') {
				if($doneFirst) {
					echo ',';
				} else {
					$doneFirst = true;
				}
				echo '{"key":"'.$split[0].'","src":"/static/images/emoticons/'.$filename.'"}';
			}
		}
	}
	echo ']';

	closedir($dh);
}



