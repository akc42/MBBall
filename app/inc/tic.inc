<?php
/*
 	Copyright (c) 2008,2009 Alan Chandler
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
require_once('./team.inc');

?><table>
	<caption>List of teams in competition</caption>
	<thead>
		<tr>
			<th>\</th><th>Division</th>
<?php
foreach($divs as $division) {
	// for each division we are building a team id, name and logo columns
?>			<th rowspan="2" colspan="3"><?php echo $division;?></th>
<?php
}
?>		</tr>		
		<tr>
			<th>Conference</th><th>\</th>
		</tr>	
	</thead>
	<tbody>
<?php
foreach($confs as $confid => $conference) {
	$no_of_rows = max($sizes[$confid]);
?>		<tr>
			<td class="conference" colspan="2" rowspan="<?php echo ($no_of_rows == 0)?1:$no_of_rows;?>"><?php echo $conference;?></td>
<?php
	if($no_of_rows != 0) {
		for ($i = 0; $i < $no_of_rows;$i++) {
			if( $i != 0) {
?>		<tr>
<?php
			}
			foreach($divs as $divid => $division) {
				if(isset($teams[$confid][$divid][$i])) { //only if this entry is set
					$row=$teams[$confid][$divid][$i];
					if($row['mp']) {
?>			<td class="win">
<?php
						echo $row['tid'];tick();
					} else {
?>			<td>
<?php						
						echo $row['tid'];
					}
?>			</td>
			<td>
<?php
					if (!is_null($row['url'])) {
							// if we have a url for team provide a link for it
						echo '<a href="'.$row['url'].'">'.$row['name'].'</a>';
					} else {
						echo $row['name'];
					}
?>			</td>
			<td>
<?php 
					if(!is_null($row['logo'])) {
						$logopath = MBBALL_ICON_PATH.$row['logo'];
						if (!is_null($row['url'])) {			
							echo '<a href="'.$row['url'].'"><img src="'.$logopath.'" alt="team logo" /></a>';
						} else {
							echo '<img src="'.$logopath.'" alt="team logo" />';
						}
					}
?>			</td>
<?php
				} else {
?>			<td colspan="3"></td>
<?php
				}
			}
		}
	} else {
?>			<td colspan="<?php echo 3*count($divs);?>"><p class="no_team">No Teams in this conference (or they have not yet been selected by the administrator)<p></td>
<?php
	} 
?>		</tr>
<?php
}

?>	</tbody>
</table>
