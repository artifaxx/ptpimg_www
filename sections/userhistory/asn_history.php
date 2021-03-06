<?
/************************************************************************
||------------|| User IP history page ||---------------------------||

This page lists previous IPs a user has connected to the site with. It
gets called if $_GET['action'] == 'ips'.

It also requires $_GET['userid'] in order to get the data for the correct
user.

************************************************************************/

define('ASN_PER_PAGE', 100);

if(!check_perms('users_view_ips')) { error(403); }

$UserID = $_GET['userid'];
if (!is_number($UserID)) { error(404); }
$UsersOnly = $_GET['usersonly'];

$DB->query("SELECT UserName FROM users_main WHERE ID = $UserID");
list($Username) = $DB->next_record();

show_header("ASN history for $Username");
?>
<div class="thin">
<?
list($Page,$Limit) = page_limit(ASN_PER_PAGE);

if ($UsersOnly == 1) {
	$RS = $DB->query("SELECT SQL_CALC_FOUND_ROWS
	        h1.ASN,
	       	h1.StartTime,
	       	h1.EndTime,
	        GROUP_CONCAT(h2.UserID SEPARATOR '|'),
	        GROUP_CONCAT(h2.StartTime SEPARATOR '|'),
	        GROUP_CONCAT(h2.EndTime SEPARATOR '|'),
	        GROUP_CONCAT(um2.Username SEPARATOR '|'),
	   	GROUP_CONCAT(um2.Enabled SEPARATOR '|'),
	        GROUP_CONCAT(ui2.Donor SEPARATOR '|'),
	        GROUP_CONCAT(ui2.Warned SEPARATOR '|')
	        FROM users_history_asns AS h1
	        LEFT JOIN users_history_asns AS h2 ON h2.ASN=h1.ASN AND h2.UserID!=$UserID
	        LEFT JOIN users_main AS um2 ON um2.ID=h2.UserID
	        LEFT JOIN users_info AS ui2 ON ui2.UserID=h2.UserID
		WHERE h1.UserID='$UserID'
		AND h2.UserID>0
	        GROUP BY h1.ASN, h1.StartTime
		ORDER BY h1.StartTime DESC LIMIT $Limit");
} else {
	$RS = $DB->query("SELECT SQL_CALC_FOUND_ROWS
		h1.ASN, 
		h1.StartTime, 
		h1.EndTime,
		GROUP_CONCAT(h2.UserID SEPARATOR '|'),
		GROUP_CONCAT(h2.StartTime SEPARATOR '|'),
		GROUP_CONCAT(h2.EndTime SEPARATOR '|'),
		GROUP_CONCAT(um2.Username SEPARATOR '|'),
		GROUP_CONCAT(um2.Enabled SEPARATOR '|'),
		GROUP_CONCAT(ui2.Donor SEPARATOR '|'),
		GROUP_CONCAT(ui2.Warned SEPARATOR '|')
		FROM users_history_asns AS h1
		LEFT JOIN users_history_asns AS h2 ON h2.ASN=h1.ASN AND h2.UserID!=$UserID
		LEFT JOIN users_main AS um2 ON um2.ID=h2.UserID
		LEFT JOIN users_info AS ui2 ON ui2.UserID=h2.UserID
		WHERE h1.UserID='$UserID'
		GROUP BY h1.ASN, h1.StartTime
		ORDER BY h1.StartTime DESC LIMIT $Limit");
}
$DB->query("SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();
$DB->set_query_id($RS);

$Pages=get_pages($Page,$NumResults,ASN_PER_PAGE,9);

?>
	<h2>ASN history for <a href="/user.php?id=<?=$UserID?>"><?=$Username?></a></h2>
	<div class="linkbox"><?=$Pages?></div>
	<table>
		<tr class="colhead">
			<td>ASN</td>
			<td>Started</td>
			<td>Ended</td>
			<td>Elapsed</td>
		</tr>
<?
$Results = $DB->to_array();
foreach($Results as $Result) {
	list($ASN, $StartTime, $EndTime, $UserIDs, $UserStartTimes, $UserEndTimes, $Usernames, $UsersEnabled, $UsersDonor, $UsersWarned) = $Result;
	$HasDupe = false;
	$UserIDs = explode('|', $UserIDs);
	if(!$EndTime) { $EndTime = sqltime(); }
	if($UserIDs[0] != 0){
		$HasDupe = true;
		$UserStartTimes = explode('|', $UserStartTimes);
		$UserEndTimes = explode('|', $UserEndTimes);
		$Usernames = explode('|', $Usernames);
		$UsersEnabled = explode('|', $UsersEnabled);
		$UsersDonor = explode('|', $UsersDonor);
		$UsersWarned = explode('|', $UsersWarned);
	}
?>
		<tr class="rowa">
			<td><?=$ASN?></td>
			<td><?=time_diff($StartTime)?></td>
			<td><?=time_diff($EndTime)?></td>
			<td><?//time_diff(strtotime($StartTime), strtotime($EndTime)); ?></td>
		</tr>
<?
	if($HasDupe){
		foreach ($UserIDs as $Key => $Val) {
		if(!$UserEndTimes[$Key]){ $UserEndTimes[$Key] = sqltime(); }
?>
		<tr class="rowb">
			<td>&nbsp;&nbsp;&#187;&nbsp;<?=format_username($Val, $Usernames[$Key], $UsersDonor[$Key], $UsersWarned[$Key], $UsersEnabled[$Key] == 2 ? false : true)?></td>
			<td><?=time_diff($UserStartTimes[$Key])?></td>
			<td><?=time_diff($UserEndTimes[$Key])?></td>
			<td><?//time_diff(strtotime($UserStartTimes[$Key]), strtotime($UserEndTimes[$Key])); ?></td>
		</tr>
<?
			
		}
	}
?>
<?
}
?>
	</table>
	<div class="linkbox">
		<?=$Pages?>
	</div>
</div>

<?
show_footer();
?>
