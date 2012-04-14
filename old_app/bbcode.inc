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
//  These are the patterns that we will use to search for some simple bbcode sequences
$search[0]='/\[b\](.*?)\[\/b\]/';
$search[1]='/\[s\](.*?)\[\/s\]/';
$replace[0]='<b>$1</b>';
$replace[1]='<del>$1</del>';
$dir = $_SERVER['DOCUMENT_ROOT'].'/static/images/emoticons';
$fns = scandir($dir);
foreach ($fns as $filename) {
	if(filetype($dir.'/'.$filename) == 'file') {
		$split = splitFIlename($filename);
		if($split[1] == 'gif') {
			$search[] = '/:'.$split[0].'/';
			$replace[] = '<img src="/static/images/emoticons/'.$filename.'" alt=":'.$split[0].'" title="'.$split[0].'" />';
		}
	}
}

unset($dir);
unset($fns);



function dbBBcode($text) {
	global $search,$replace;
	return preg_replace($search,$replace,$text);
}
?>
