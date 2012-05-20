<?php
/*
 	Copyright (c) 2008-2011 Alan Chandler
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
?><div id="emoticons">
<?php
// Open a known directory, and proceed to read its contents

	$fns = scandir(MBBALL_EMOTICON_DIR);
	foreach ($fns as $filename) {
		if(filetype($dir.'/'.$filename) == 'file') {
			$split = splitFIlename($filename);
			if($split[1] == 'gif') {
				echo '<img src="'.MBBALL_EMOTICON_URL.'/'.$filename.'" alt=":'.$split[0].'" title="'.$split[0].'" />',"\n";
			}
		}
	}
?></div>