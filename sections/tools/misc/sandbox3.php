<?
die("seeding time, check source");
// Show seeding time
if(!is_number($_GET['userid'])) error(404);

$UserID=$_GET['userid'];

function statusMessage($ID) {
	switch($ID) {
		case 0:
			return "Inactive";
			break;
		case 1:
			return "Active";
			break;
		case 2:
			return "Complete";
			break;
		default:
			return "Unknown";
	}
}

$Origin=$DB->query("SELECT
				um.ID,
				um.Username,
				t.ID,
				t.GroupID,
				tg.Name,
				usr.StartTime,
				usr.Status,
				usr.StatusChange,
				usr.Requirement,
				usr.Completed,
				usr.GracePeriodExpires
				FROM users_seedreqs AS usr
				LEFT JOIN users_main AS um ON um.ID=usr.UserID
				LEFT JOIN torrents AS t ON t.ID=usr.TorrentID
				LEFT JOIN torrents_group AS tg ON tg.ID=t.GroupID
				WHERE usr.UserID=$UserID");

$DB->query("SELECT AVG(Completed) FROM users_seedreqs WHERE UserID=$UserID AND Status='2'");
list($Average)=$DB->next_record();
if(!$Average) $Average=0;
$DB->query("SELECT count(Status) FROM users_seedreqs WHERE UserID=$UserID AND Status='0' or Status='1'");
list($Pending)=$DB->next_record();
if(!$Pending) $Pending=0;
?>
<p>Your seeding time average: <b><?=$Average/60?></b> hours.</p>
<p>You have <b><?=$Pending?></b> pending torrents.</p>
<table>
	<tr>
		<th>UserID/Username</th>
		<th>Title</th>
		<th>Snatch Time</th>
		<th>Status</th>
		<th>Last Status Change</th>
		<th>Time Requirement</th>
		<th>Time Achieved</th>
		<th>Seeding Amount</th>
		<th>Grace Period Expires</th>
	</tr>
	
<?
	$DB->set_query_id($Origin);
	while(list($UserID, $Username, $TorrentID, $GroupID, $Title, $StartTime, $Status, $StatusChange, $Requirement, $Completed, $GracePeriodExpires)=$DB->next_record()) { 
?>
	<tr>
		<td><a href="user.php?id=<?=$UserID?>"><?=$Username?></a></td>
		<td><a href="torrents.php?id=<?=$GroupID?>&torrentid=<?=$TorrentID?>"><?=$Title?></a></td>
		<td><?=time_diff($StartTime)?></td>
		<td><?=statusMessage($Status)?></td>
		<td><?=time_diff($StatusChange)?></td>
		<td><?=($Requirement/60)?> hours</td>
		<td><?=($Completed/60)?> hours</td>
		<td><?=round(($Completed/$Requirement)*100,2)?>%</td>
		<td><?=time_diff($GracePeriodExpires)?></td>
	</tr>
<? } ?>
</table>