<?
if(!check_perms('users_view_ips') || !check_perms('users_view_email')) { error(403); }
show_header('Bonus log');
define('USERS_PER_PAGE', 50);
list($Page,$Limit) = page_limit(USERS_PER_PAGE);

if (!empty($_GET['username'])) {
	$DB->query("SELECT ID FROM users_main WHERE Username='".db_string($_GET['username'])."' LIMIT 1");
	list($UserID)=$DB->next_record();
	$ExtraCase = "AND (b.UserID = $UserID OR b.OtherID = $UserID)";
}


if (!empty($_GET['amount'])) {
	$o=substr($_GET['amount'], 0, 1);
	switch ($o) {
		case '<':
			$Amount='AND b.Points < '.substr($_GET['amount'], 1);
			break;
		case '>':
			$Amount='AND b.Points > '.substr($_GET['amount'], 1);
			break;
		case '=':
			$Amount='AND b.Points = '.substr($_GET['amount'], 1);
		break;
		default:
			if (is_number($_GET['amount'])) {
				$Amount='AND b.Points = '.$_GET['amount'];
			}
	}
}

?>
<table>
<tr>
	<td>
		<form action="tools.php" method="get">
			<input type="hidden" name="action" value="bplog" />
			amount: <input type="text" name="amount" value="<?=!empty($_GET['amount']) ? $_GET['amount'] : '' ?>" />
			<input type="submit" value="narrow" />
		</form>
	</td>
	<td>
		<form action="tools.php" method="get">
			<input type="hidden" name="action" value="bplog" />
			username: <input type="text" name="username" value="<?=!empty($_GET['username']) ? $_GET['username'] : '' ?>" />
			<input type="submit" value="narrow" />
		</form>
	</td>
</tr>
</table>

<?


$RS = $DB->query("SELECT 
	SQL_CALC_FOUND_ROWS
	b.EntryID,
	b.UserID,
	b.Points,
	b.Type,
	b.OtherID,
	b.Timestamp,
	m.ID,
	m.IP,
	m.Username,
	m.PermissionID,
	m.Uploaded,
	m.Downloaded,
	m.Enabled,
	i.Donor,
	i.Warned,
	i.JoinDate,
	im.ID,
	im.IP,
	im.Username,
	im.PermissionID,
	im.Uploaded,
	im.Downloaded,
	im.Enabled,
	ii.Donor,
	ii.Warned,
	ii.JoinDate
	FROM bp_log AS b
	LEFT JOIN users_main AS m ON b.UserID=m.ID
	LEFT JOIN users_info AS i ON i.UserID=m.ID
	LEFT JOIN users_main AS im ON b.OtherID = im.ID
	LEFT JOIN users_info AS ii ON b.OtherID = ii.UserID
	WHERE b.UserID>0 AND b.OtherID>0 AND Type='1'
	$Amount
	$ExtraCase
	ORDER BY b.EntryID DESC LIMIT $Limit");
$DB->query("SELECT FOUND_ROWS()");
list($Results) = $DB->next_record();
$DB->set_query_id($RS);

if($DB->record_count()) {
?>
	<div class="linkbox">
<?
	$Pages=get_pages($Page,$Results,USERS_PER_PAGE,11) ;
	echo $Pages;
?>
	</div>
	<table width="100%">
		<tr class="colhead">
			<td>User</td>
			<td>Ratio</td>
			<td>Amount</td>
			<td>IP</td>
			<td>Host</td>
			<td>Registered</td>
			<td>Time</td>
		</tr>
<?
/*
	b.EntryID,
	b.UserID,
	b.Points,
	b.Type,
	b.OtherID,
	b.Timestamp,
	m.ID,
	m.IP,
	m.Username,
	m.PermissionID,
	m.Uploaded,
	m.Downloaded,
	m.Enabled,
	i.Donor,
	i.Warned,
	i.JoinDate,
	im.ID,
	im.IP,
	im.Username,
	im.PermissionID,
	im.Uploaded,
	im.Downloaded,
	im.Enabled,
	ii.Donor,
	ii.Warned,
	ii.JoinDate
	
	*/
	while(list($EntryID, $UserID, $Value, $Type, $OtherID, $TimeStamp, $UserID2, $IP, $Username, $PermissionID, $Uploaded,
	$Downloaded, $Enabled, $Donor, $Warned, $Joined,
	$OtherID2, $OtherIP, $OtherUsername, $OtherPermissionID, $OtherUploaded, $OtherDownloaded, $OtherEnabled,
	$OtherDonor, $OtherWarned, $OtherJoined, $OtherUses)=$DB->next_record()) {
	$Row = ($IP == $OtherIP) ? 'a' : 'b';
?>
		<tr class="row<?=$Row?>">
			<td><?=format_username($UserID, $Username, $Donor, $Warned, $Enabled, $PermissionID)?><br /><?=format_username($OtherID, $OtherUsername, $OtherDonor, $OtherWarned, $OtherEnabled, $OtherPermissionID)?></td>
			<td><?=ratio($Uploaded,$Downloaded)?><br /><?=ratio($OtherUploaded,$OtherDownloaded)?></td>
			<td>
				<span style="float:left;"><?=number_format($Value)?></span>
			</td>
			<td>
				<span style="float:left;"><?=display_str($IP)?></span>
				<span style="float:right;"><?=display_str($Uses)?> [<a href="userhistory.php?action=ips&amp;userid=<?=$UserID?>" title="History">H</a>|<a href="/user.php?action=search&amp;ip_history=on&amp;ip=<?=display_str($IP)?>" title="Search">S</a>]</span><br />
				<span style="float:left;"><?=display_str($OtherIP)?></span>
				<span style="float:right;"><?=display_str($InviterUses)?> [<a href="userhistory.php?action=ips&amp;userid=<?=$InviterID?>" title="History">H</a>|<a href="/user.php?action=search&amp;ip_history=on&amp;ip=<?=display_str($InviterIP)?>" title="Search">S</a>]</span><br />
			</td>
			<td>
				<?=get_host($IP)?><br />
				<?=get_host($OtherIP)?>
			</td>
			<td><?=time_diff($Joined)?><br /><?=time_diff($OtherJoined)?></td>
			<td><?=time_diff($TimeStamp)?></td>
		</tr>
<?	} ?>
	</table>
	<div class="linkbox">
<? echo $Pages; ?>
	</div>
<? } else { ?>
	<h2 align="center">There have been no new registrations in the past 72 hours.</h2>
<? }
show_footer();
?>
