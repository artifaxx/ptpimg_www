<?
// Show seeding time
if(!is_number($_GET['userid'])) $UserID=$LoggedUser['ID'];
else $UserID=$_GET['userid'];

if(!check_perms('users_mod') && ($UserID!=$LoggedUser['ID'])) error(403);

function statusMessage($ID) {
	switch($ID) {
		case 0:
			return "<strong style='color:red;'>Inactive</strong>";
			break;
		case 1:
			return "<strong style='color:orange;'>Active</strong>";
			break;
		case 2:
			return "<strong style='color:green;'>Complete</strong>";
			break;
		default:
			return "Unknown";
	}
}

show_header(($UserID!=$LoggedUser['ID'])?'Seeding times for '.$UserID:'Seeding Times','seedtime');

$Origin=$DB->query("SELECT
				um.ID,
				um.Username,
				t.ID,
				t.Codec,
				t.Container,
				t.Resolution,
				t.Source,
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
				WHERE usr.UserID=$UserID
				AND usr.Status='2'");
//$CompletedCount=$DB->record_count();
$DataCompleted=$DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($CompletedCount)=$DB->next_record();

$Origin2=$DB->query("SELECT
				um.ID,
				um.Username,
				t.ID,
				t.Codec,
				t.Container,
				t.Resolution,
				t.Source,
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
				WHERE usr.UserID=$UserID
				AND usr.Status='1'");
$DataActive=$DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($ActiveCount)=$DB->next_record();

$Origin3=$DB->query("SELECT
				um.ID,
				um.Username,
				t.ID,
				t.Codec,
				t.Container,
				t.Resolution,
				t.Source,
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
				WHERE usr.UserID=$UserID
				AND usr.Status='0'");
$DataInactive=$DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($InactiveCount)=$DB->next_record();

$DB->query("SELECT AVG(Completed) FROM users_seedreqs WHERE UserID=$UserID AND Status='2'");
list($Average)=$DB->next_record();
if(!$Average) $Average=0;
$DB->query("SELECT count(Status) FROM users_seedreqs WHERE UserID=$UserID AND (Status='0' or Status='1')");
list($Pending)=$DB->next_record();
if(!$Pending) $Pending=0;

function timeDisplay($c,$r) {
	$Value=($r-$c)/60;
	if($Value<1) 
		return (60*$Value)." minutes";
	else
		return $Value." hours";
}
?>
<div class="thin">
	<h3 id="seedtime">Seeding Times</h3>
	<div class="box pad" style="padding: 10px 10px 10px 20px;">
		<p align="center">Your seeding time average: <b><?=round($Average/60,2)?></b> hours.</p>
		<p align="center">You have <b><?=$Pending?></b> pending torrents.</p>
	</div>
<? if ($CompletedCount) { ?>
<p><a href="javascript:collapse('seedreq_completed', 'collapse:sreq_completed');">Toggle Completed! (<?=$CompletedCount?>)</a></p>
<div id="seedreq_completed" class="<?=($HeavyInfo['collapse:sreq_completed']=='true')?'hidden':''?>">
<table>
	<tr class="rowa">
		<th>Title</th>
		<th>Snatch Time</th>
		<th>Status</th>
		<th>Last Status Change</th>
		<th>Seeded Time</th>
		<th>Time Left</th>
<? if (check_perms('users_mod')) { ?>
		<th>Admin</th>
<? } ?>
	</tr>
	
<?
	$Row = 'a';
	while(list($Key,list($UserID, $Username, $TorrentID, $Codec, $Container, $Resolution, $Source, $GroupID, $GroupName, $StartTime, $Status, $StatusChange, $Requirement, $Completed, $GracePeriodExpires))=each($DataCompleted)) { 
		$Row = ($Row === 'a' ? 'b' : 'a');
		$ExtraInfo='';
		$AddExtra='';
		if($Codec) { $ExtraInfo.=$AddExtra.display_str($Codec); $AddExtra=' / '; }
        if($Container) { $ExtraInfo.=$AddExtra.display_str($Container); $AddExtra=' / '; }
        if($Source) { $ExtraInfo.=$AddExtra.display_str($Source); $AddExtra=' / '; }
        if($Resolution) { $ExtraInfo.=$AddExtra.display_str($Resolution); $AddExtra=' / '; }
	
		$Artists = get_artist($GroupID);

		if($Artists) {
			$ArtistName = display_artists($Artists, true).$DisplayName;
		}
?>
	<tr class="row<?=$Row?>">
		<td id="s_<?=$TorrentID?>_n"><?=$ArtistName?> <a href="torrents.php?id=<?=$GroupID?>&torrentid=<?=$TorrentID?>"><?=$GroupName?></a><br /><?=$ExtraInfo?></td>
		<td id="s_<?=$TorrentID?>_t"><?=time_diff($StartTime)?></td>
		<td id="s_<?=$TorrentID?>_s"><?=statusMessage($Status)?></td>
		<td id="s_<?=$TorrentID?>_l"><?=time_diff($StatusChange)?></td>
		<td id="s_<?=$TorrentID?>_h" style="<? if ($Completed<$Average) { echo 'color:red'; } else { echo 'color:green'; }?>"><?=floor($Completed/60)?> hours</td>
		<td id="s_<?=$TorrentID?>_d"><?=($Requirement>$Completed) ? timeDisplay($Completed,$Requirement)." (".round(($Completed/$Requirement)*100,2)."%)":"0 hours"?></td>
<? if (check_perms('users_mod')) { ?>
		<td>
			<input type="button" id="b_<?=$TorrentID?>_c" value="Chg" onclick="change(<?=$UserID?>,<?=$TorrentID?>,<?=floor($Completed/60)?>);"/>
			<input type="button" id="b_<?=$TorrentID?>_a" value="MakeActive" onclick="mkactive(<?=$UserID?>, <?=$TorrentID?>);"/>
            <input type="button" id="b_<?=$TorrentID?>_i" value="Complete" onclick="mkinactive(<?=$UserID?>,<?=$TorrentID?>);"/>

		</td>
<? } ?>
	</tr>
<? } ?>
</table>
</div>

<? } // CompletedCount ?>
<? if ($ActiveCount) { ?>
<p><a href="javascript:collapse('seedreq_active', 'collapse:sreq_active');">Toggle Active! (<?=$ActiveCount?>)</a></p>
<div id="seedreq_active" class="<?=($HeavyInfo['collapse:sreq_active']=='true')?'hidden':''?>">

<table>
	<tr class="rowa">
		<th>Title</th>
		<th>Snatch Time</th>
		<th>Status</th>
		<th>Last Status Change</th>
		<th>Seeded Time</th>
		<th>Time Left</th>
<? if (check_perms('users_mod')) { ?>
		<th>Admin</th>
<? } ?>
	</tr>
	
<?
	$DB->set_query_id($Origin2);
	$Row = 'a';
	while(list($Key,list($UserID, $Username, $TorrentID, $Codec, $Container, $Resolution, $Source, $GroupID, $GroupName, $StartTime, $Status, $StatusChange, $Requirement, $Completed, $GracePeriodExpires))=each($DataActive)) { 
		$Row = ($Row === 'a' ? 'b' : 'a');
		$ExtraInfo='';
		$AddExtra='';
		if($Codec) { $ExtraInfo.=$AddExtra.display_str($Codec); $AddExtra=' / '; }
        if($Container) { $ExtraInfo.=$AddExtra.display_str($Container); $AddExtra=' / '; }
        if($Source) { $ExtraInfo.=$AddExtra.display_str($Source); $AddExtra=' / '; }
        if($Resolution) { $ExtraInfo.=$AddExtra.display_str($Resolution); $AddExtra=' / '; }
	
		$Artists = get_artist($GroupID);

		if($Artists) {
			$ArtistName = display_artists($Artists, true).$DisplayName;
		}
?>
	<tr class="row<?=$Row?>">
		<td id="s_<?=$TorrentID?>_n"><?=$ArtistName?> <a href="torrents.php?id=<?=$GroupID?>&torrentid=<?=$TorrentID?>"><?=$GroupName?></a><br /><?=$ExtraInfo?></td>
		<td id="s_<?=$TorrentID?>_t"><?=time_diff($StartTime)?></td>
		<td id="s_<?=$TorrentID?>_s"><?=statusMessage($Status)?></td>
		<td id="s_<?=$TorrentID?>_l"><?=time_diff($StatusChange)?></td>
		<td id="s_<?=$TorrentID?>_h" style="<? if ($Completed<$Average) { echo 'color:red'; } else { echo 'color:green'; }?>"><?=floor($Completed/60)?> hours</td>
		<td id="s_<?=$TorrentID?>_d"><?=($Requirement>$Completed) ? timeDisplay($Completed,$Requirement)." (".round(($Completed/$Requirement)*100,2)."%)":"0 hours"?></td>
<? if (check_perms('users_mod')) { ?>
		<td>
			<input type="button" id="b_<?=$TorrentID?>_c" value="Chg" onclick="change(<?=$UserID?>,<?=$TorrentID?>,<?=floor($Completed/60)?>);"/>
			<input type="button" id="b_<?=$TorrentID?>_a" value="MakeActive" onclick="mkactive(<?=$UserID?>, <?=$TorrentID?>);"/>
            <input type="button" id="b_<?=$TorrentID?>_i" value="Complete" onclick="mkinactive(<?=$UserID?>,<?=$TorrentID?>);"/>

		</td>
<? } ?>
	</tr>
<? } ?>
</table>
</div>
<? } // ActiveCount ?>
<? if ($InactiveCount) { ?>
<p><a href="javascript:collapse('seedreq_inactive', 'collapse:sreq_inactive');">Toggle Inactive! (<?=$InactiveCount?>)</a></p>
<div id="seedreq_inactive" class="<?=($HeavyInfo['collapse:sreq_inactive']=='true')?'hidden':''?>">

<table>
	<tr class="rowa">
		<th>Title</th>
		<th>Snatch Time</th>
		<th>Status</th>
		<th>Last Status Change</th>
		<th>Seeded Time</th>
		<th>Time Left</th>
<? if (check_perms('users_mod')) { ?>
		<th>Admin</th>
<? } ?>
	</tr>
	
<?
	$DB->set_query_id($Origin3);
	$Row = 'a';
	while(list($Key,list($UserID, $Username, $TorrentID, $Codec, $Container, $Resolution, $Source, $GroupID, $GroupName, $StartTime, $Status, $StatusChange, $Requirement, $Completed, $GracePeriodExpires))=each($DataInactive)) { 
		$Row = ($Row === 'a' ? 'b' : 'a');
		$ExtraInfo='';
		$AddExtra='';
		if($Codec) { $ExtraInfo.=$AddExtra.display_str($Codec); $AddExtra=' / '; }
        if($Container) { $ExtraInfo.=$AddExtra.display_str($Container); $AddExtra=' / '; }
        if($Source) { $ExtraInfo.=$AddExtra.display_str($Source); $AddExtra=' / '; }
        if($Resolution) { $ExtraInfo.=$AddExtra.display_str($Resolution); $AddExtra=' / '; }
	
		$Artists = get_artist($GroupID);

		if($Artists) {
			$ArtistName = display_artists($Artists, true).$DisplayName;
		}
?>
	<tr class="row<?=$Row?>">
		<td id="s_<?=$TorrentID?>_n"><?=$ArtistName?> <a href="torrents.php?id=<?=$GroupID?>&torrentid=<?=$TorrentID?>"><?=$GroupName?></a><br /><?=$ExtraInfo?></td>
		<td id="s_<?=$TorrentID?>_t"><?=time_diff($StartTime)?></td>
		<td id="s_<?=$TorrentID?>_s"><?=statusMessage($Status)?></td>
		<td id="s_<?=$TorrentID?>_l"><?=time_diff($StatusChange)?></td>
		<td id="s_<?=$TorrentID?>_h" style="<? if ($Completed<$Average) { echo 'color:red'; } else { echo 'color:green'; }?>"><?=floor($Completed/60)?> hours</td>
		<td id="s_<?=$TorrentID?>_d"><?=($Requirement>$Completed) ? timeDisplay($Completed,$Requirement)." (".round(($Completed/$Requirement)*100,2)."%)":"0 hours"?></td>
<? if (check_perms('users_mod')) { ?>
		<td>
			<input type="button" id="b_<?=$TorrentID?>_c" value="Chg" onclick="change(<?=$UserID?>,<?=$TorrentID?>,<?=floor($Completed/60)?>);"/>
			<input type="button" id="b_<?=$TorrentID?>_a" value="MakeActive" onclick="mkactive(<?=$UserID?>, <?=$TorrentID?>);"/>
            <input type="button" id="b_<?=$TorrentID?>_i" value="Complete" onclick="mkinactive(<?=$UserID?>,<?=$TorrentID?>);"/>

		</td>
<? } ?>
	</tr>
<? } ?>
</table>
</div>
<? } ?>
</div>
<? show_footer(); ?>
