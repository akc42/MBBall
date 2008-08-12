<?php
if (!defined('BALL'))
	die('Hacking attempt...');
require_once('team.php');

?><table>
	<caption>List of teams in competition</caption>
	<thead>
		<tr>
			<th class="po_h1">\</th><th class="po_h2">Division</th>
<?php
foreach($divs as $division) {
	// for each division we are building a team id, name and logo columns
?>			<th class="t_dn" rowspan="2" colspan="3"><?php echo $division;?></th>
<?php
}
?>		</tr>		
		<tr>
			<th class="po_h3">Conference</th><th class="po_h4">\</th>
		</tr>	
	</thead>
	<tbody>
<?php
foreach($confs as $confid => $conference) {
	$no_of_rows = max($sizes[$confid]);
?>		<tr>
			<td class="po_b1" colspan="2" rowspan="<?php echo $no_of_rows;?>"><?php echo $conference;?></td>
<?php
	for ($i = 0; $i < $no_of_rows;$i++) {
		if( $i != 0) {
?>		<tr>
<?php
		}
		foreach($divs as $divid => $division) {
			if(isset($teams[$confid][$divid][$i])) { //only if this entry is set
				$row=$teams[$confid][$divid][$i];
				if($row['mp']) {
?>			<td class="in_po">
<?php
			   echo $row['tid'].'<span class="in_po"><br/>(In PO)</span>';
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
?>		</tr>
<?php
	}
}
?>	</tbody>
</table>
