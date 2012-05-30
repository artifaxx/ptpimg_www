<?php
// Leecher watch
// Originally written by z for PTP, ported over to MusicEye by Danger
//if(!check_perms('users_view_ips') || !check_perms('users_view_email')) { error(403); }
enforce_login();
show_header("Ghost-Leech detection");

if (isset($_GET['mode'])) $mode=$_GET['mode']; else $mode="view1";

$DB->query("SELECT
                        um.ID,
						um.Username,
						um.Downloaded,
						um.Uploaded,
						p.Level AS Class
                    FROM users_main AS um
					JOIN permissions AS p ON p.ID=um.PermissionID
                    WHERE um.Enabled='1'
					ORDER BY um.Uploaded DESC");
$Results = $DB->to_array();
?>

<div class="box pad" align="center">
<a href="tools.php?action=leech&mode=view1">view mode 1</a> | <a href="tools.php?action=leech&mode=view2">view mode 2</a>
<br />
<?
switch ($mode) {
	case "view1":
		echo "downloaded .torrent files, but didn't have any download amounts";
		break;
	case "view2":
		echo "downloaded .torrent files, but didn't have any snatches";
		break;
		
}
?>
</div>

<table>
<tr>
<th>Username</th>
<th>Downloaded</th>
<th>Uploaded</th>
<th>Ratio</th>
<th>Download Count</th>
<th>Uploads</th>
</tr>
<?php

while(list($Key,list($ID,$Username,$Downloaded,$Uploaded,$Class))=each($Results)) {
if ($Class>400) continue;
if ($mode=="view1") {
	if ($Downloaded>0) continue;
}
elseif ($mode=="view2") {
	$DB->query("SELECT COUNT(x.uid) FROM xbt_snatched AS x WHERE x.uid='".$ID."'");
	list($SnatchCount)=$DB->next_record();
	if ($SnatchCount>0) continue;
}
$DB->query("SELECT COUNT(ud.UserID) FROM users_downloads AS ud WHERE ud.UserID='".db_string($ID)."'");
list($DownloadCount)=$DB->next_record();
if (empty($DownloadCount)) continue;
$DB->query("SELECT COUNT(t.ID) FROM torrents AS t WHERE t.UserID='".db_string($ID)."'");
list($Uploads)=$DB->next_record();
?>
	<tr>
		<td><a href="user.php?id=<?=$ID?>"><?=$Username?></a> (<?=$ClassLevels[$Class]['Name']?>)</td>
		<td><?=get_size($Downloaded)?></td>
		<td><?=get_size($Uploaded)?></td>
		<td><?=ratio($Uploaded,$Downloaded)?></td>
		<td><?=$DownloadCount?></td>
		<td><?=$Uploads?></td>
	</tr>
<?php
}
?>
</table>
<?php
show_footer();
?>
