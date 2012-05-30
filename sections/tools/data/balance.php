<?
show_header('Balance Review');

if(isset($_GET['precision']) && !empty($_GET['precision'])) {
	switch($_GET['precision']) {
		case '1':
			$Precision='1 hour';
			break;
		case '2':
			$Precision='12 hour';
			break;
		case '3':
			$Precision='24 hour';
			break;
		case '4':
			$Precision='48 hour';
			break;
		default:
			$Precision='1 hour';
	}
} else {
	// Very precise
	$Precision='1 hour';
}

$UserWhere='';

if(isset($_GET['userid']) && is_number($_GET['userid'])) {
	$UserWhere='AND um.ID='.db_string($_GET['userid']);
}

$DB->query("SELECT
            bal.UserID,
            bal.Uploaded,
            bal.Downloaded,
            bal.LastChange,
			innertbl.tmpk,
			t.Leechers,
            um.Username,
			bal.TorrentID AS TorrentID,
			innertbl2.tmpz
            FROM balance AS bal
            LEFT JOIN users_main AS um ON um.ID=bal.UserID
            LEFT JOIN torrents AS t ON t.ID=bal.TorrentID,
				(SELECT fid, FROM_UNIXTIME(max(tstamp)) AS tmpk FROM xbt_snatched GROUP BY fid) AS innertbl,
				(SELECT torrentid AS tid, max(Time) AS tmpz FROM users_downloads GROUP BY torrentid) AS innertbl2
            WHERE innertbl.fid = TorrentID
			AND innertbl2.tid = TorrentID
            AND bal.LastChange > date_add(innertbl.tmpk, interval ".$Precision.")
			AND um.Enabled='1'
			$UserWhere
			ORDER BY bal.Uploaded DESC");
$Results=$DB->to_array();
?>
<table>
<tr>
<th>Username</th>
<th>Downloaded</th>
<th>Uploaded</th>
<th>Leechers</th>
<th>LastChange (User->Tracker)</th>
<th>Last Snatch/Download Time</th>
<th>Torrent</th>
</tr>
<?php

while(list($Key,list($UserID,$Uploaded,$Downloaded,$LastChange,$TmpK,$Leechers,$Username,$TorrentID,$TmpZ))=each($Results)) {
?>
	<tr>
		<td><a href="user.php?id=<?=$UserID?>"><?=$Username?></a> (<a href="tools.php?action=balance&userid=<?=$UserID?>">+</a>)</td>
		<td><?=get_size($Downloaded)?></td>
		<td><?=get_size($Uploaded)?></td>
		<td><?=($Leechers>0)?$Leechers:'<span style="color:red;">'.$Leechers.'</span>'?></td>
		<td><?=time_diff($LastChange)?></td>
		<td><span style="font-weight:bold;color:green"><?=time_diff($TmpK)?></span> / <span style="font-weight:bold;color:red"><?=time_diff($TmpZ)?></span></td>
		<td><a href="torrents.php?torrentid=<?=$TorrentID?>"><?=$TorrentID?></a></td>
	</tr>
<?php
}
?>
</table>
<?
show_footer();
?>