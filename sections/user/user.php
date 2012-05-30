<?

include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
$Text = new TEXT;

include(SERVER_ROOT.'/sections/requests/functions.php');

if (empty($_GET['id']) || !is_numeric($_GET['id'])) { error(0); }
$UserID = $_GET['id'];



if($UserID == $LoggedUser['ID']) { 
	$OwnProfile = true;
} else { 
	$OwnProfile = false;
}

if(check_perms('users_mod')) { // Person viewing is a staff member
	$DB->query("SELECT
		m.Username,
		m.Email,
		m.LastAccess,
		m.IP,
		p.Level AS Class,
		m.Enabled,
		m.Invites,
		i.JoinDate,
		i.Info,
		i.Avatar,
		i.Country,
		i.AdminComment,
		i.Donor,
		i.Artist,
		i.Warned,
		i.SupportFor,
		i.RestrictedForums,
		i.PermittedForums,
		i.Inviter,
		inviter.Username,
		i.DisableAvatar,
		i.DisableInvites,
		i.DisablePosting,
		i.DisableForums,
		i.DisableTagging,
		i.DisableUpload,
		i.DisableWiki,
		i.DisablePM,
		i.DisableIRC,
		i.DisableRequests,
		i.HideCountryChanges,
		i.OnIRC,
		i.Country,
		i.Timezone
		FROM users_main AS m
		JOIN users_info AS i ON i.UserID = m.ID
		LEFT JOIN users_main AS inviter ON i.Inviter = inviter.ID
		LEFT JOIN permissions AS p ON p.ID=m.PermissionID
		WHERE m.ID = '".$UserID."'");

	if ($DB->record_count() == 0) { // If user doesn't exist
		header("Location: log.php?search=User+".$UserID);
	}

	list($Username,	$Email,	$LastAccess, $IP, $Class, $Enabled, $Invites, $JoinDate, $Info, $Avatar, $Country, $AdminComment, $Donor, $Artist, $Warned, $SupportFor, $RestrictedForums, $PermittedForums, $InviterID, $InviterName, $DisableAvatar, $DisableInvites, $DisablePosting, $DisableForums, $DisableTagging, $DisableUpload, $DisableWiki, $DisablePM, $DisableIRC, $DisableRequests, $DisableCountry, $OnIRC, $Country, $Timezone) = $DB->next_record(MYSQLI_NUM, array(8,11));
} else { // Person viewing is a normal user
	$DB->query("SELECT
		m.Username,
		m.Email,
		m.LastAccess,
		m.IP,
		p.Level AS Class,
		m.Enabled,
		m.Invites,
		i.JoinDate,
		i.Info,
		i.Avatar,
		i.Country,
		i.Donor,
		i.Warned,
		i.Inviter,
		i.DisableInvites,
		inviter.username,
		i.OnIRC,
		i.Country,
		i.Timezone
		FROM users_main AS m
		JOIN users_info AS i ON i.UserID = m.ID
		LEFT JOIN permissions AS p ON p.ID=m.PermissionID
		LEFT JOIN users_main AS inviter ON i.Inviter = inviter.ID
		WHERE m.ID = $UserID");

	if ($DB->record_count() == 0) { // If user doesn't exist
		header("Location: log.php?search=User+".$UserID);
	}

	list($Username, $Email, $LastAccess, $IP, $Class, $Enabled, $Invites, $JoinDate, $Info, $Avatar, $Country, $Donor, $Warned, $InviterID, $DisableInvites, $InviterName, $OnIRC, $Country, $Timezone) = $DB->next_record(MYSQLI_NUM, array(9,11));
}

// Image proxy CTs
$DisplayCustomTitle = $CustomTitle;
if(check_perms('site_proxy_images') && !empty($CustomTitle)) {
	$DisplayCustomTitle = preg_replace_callback('~src=("?)(http.+?)(["\s>])~', function($Matches) {
																		return 'src='.$Matches[1].'http'.($SSL?'s':'').'://'.SITE_URL.'/image.php?c=1&amp;i='.urlencode($Matches[2]).$Matches[3];
																	}, $CustomTitle);
}

$Paranoia = unserialize($Paranoia);
if(!is_array($Paranoia)) {
	$Paranoia = array();
}
$ParanoiaLevel = 0;
foreach($Paranoia as $P) {
	$ParanoiaLevel++;
	if(strpos($P, '+')) {
		$ParanoiaLevel++;
	}
}

$JoinedDate = time_diff($JoinDate);
$LastAccess = time_diff($LastAccess);

function check_paranoia_here($Setting) {
	global $Paranoia, $Class, $UserID;
	return check_paranoia($Setting, $Paranoia, $Class, $UserID);
}

$Badges=($Donor) ? '<a href="donate.php"><img src="'.STATIC_SERVER.'common/symbols/donor.png" alt="Donor" /></a>' : '';


$Badges.=($Warned!='0000-00-00 00:00:00') ? '<img src="'.STATIC_SERVER.'common/symbols/warned.png" alt="Warned" />' : '';
$Badges.=($Enabled == '1' || $Enabled == '0' || !$Enabled) ? '': '<img src="'.STATIC_SERVER.'common/symbols/disabled.png" alt="Banned" />';
$Badges.=(empty($Country))?'':'<img src="/static/common/avatars/blank.gif" class="flag flag-'.$Country.'" alt="'.$Countries[$Country].'" />';

show_header($Username,'user,bbcode,requests');
?>
<div class="thin">
	<h2><?=$Username?></h2>
	<div class="linkbox">
<? if (!$OwnProfile) { ?>
		[<a href="inbox.php?action=compose&amp;to=<?=$UserID?>">Send Message</a>]
		[<a href="points.php?target=<?=$Username?>">Send points!</a>]
<? 	$DB->query("SELECT FriendID FROM friends WHERE UserID='$LoggedUser[ID]' AND FriendID='$UserID'");
	if($DB->record_count() == 0) { ?>
		[<a href="friends.php?action=add&amp;friendid=<?=$UserID?>&amp;auth=<?=$LoggedUser['AuthKey']?>">Add to friends</a>]
<?	}?>
		[<a href="reports.php?action=report&amp;type=user&amp;id=<?=$UserID?>">Report User</a>]
<?

}

if (check_perms('users_edit_profiles', $Class)) {
?>
		[<a href="user.php?action=edit&amp;userid=<?=$UserID?>">Settings</a>]
<? }
if (check_perms('users_view_invites', $Class)) {
?>
		[<a href="user.php?action=invite&amp;userid=<?=$UserID?>">Invites</a>]
<? }
if (check_perms('admin_manage_permissions', $Class)) {
?>
		[<a href="user.php?action=permissions&amp;userid=<?=$UserID?>">Permissions</a>]
<? }
if (check_perms('users_logout', $Class) && check_perms('users_view_ips', $Class)) {
?>
		[<a href="user.php?action=sessions&amp;userid=<?=$UserID?>">Sessions</a>]
<? }
if (check_perms('admin_reports')) {
?>
		[<a href="reportsv2.php?view=reporter&amp;id=<?=$UserID?>">Reports</a>]
<? }
?>
	</div>

	<div class="sidebar">
<?	if ($Avatar && empty($HeavyInfo['DisableAvatars'])) {
		if(check_perms('site_proxy_images') && !empty($Avatar)) {
			$Avatar = 'http'.($SSL?'s':'').'://'.SITE_URL.'/image.php?c=1&avatar='.$UserID.'&i='.urlencode($Avatar);
		}
?>
		<div class="box">
			<div class="head colhead_dark">Avatar</div>
			<div align="center"><img src="<?=display_str($Avatar)?>" width="150" style="max-height:400px;" alt="<?=$Username?>'s avatar" /></div>
		</div>
<? } ?>
		<div class="box">
			<div class="head colhead_dark">Stats</div>
			<ul class="stats nobullet">
				<li>Joined: <?=$JoinedDate?></li>
				<li>Last Seen: <?=$LastAccess?></li>
			</ul>
		</div>
<?

	if (check_perms('users_mod', $Class) || check_perms('users_view_ips',$Class) || check_perms('users_view_keys',$Class)) {
		$DB->query("SELECT COUNT(*) FROM users_history_passwords WHERE UserID='$UserID'");
		list($PasswordChanges) = $DB->next_record();
		if (check_perms('users_view_ips',$Class)) {
			$DB->query("SELECT COUNT(DISTINCT IP) FROM users_history_ips WHERE UserID='$UserID'");
			list($IPChanges) = $DB->next_record();
			$DB->query("SELECT COUNT(DISTINCT ASN) FROM users_history_asns WHERE UserID='$UserID'");
			list($ASNChanges) = $DB->next_record();
			$DB->query("SELECT COUNT(DISTINCT Cidr) FROM users_history_cidr WHERE UserID='$UserID'");
			list($CidrChanges) = $DB->next_record();
			$DB->query("SELECT COUNT(DISTINCT CountryCode) FROM users_history_country WHERE UserID='$UserID'");
			list($CountryChanges) = $DB->next_record();
		}
		if (check_perms('users_view_email',$Class)) {
			$DB->query("SELECT COUNT(*) FROM users_history_emails WHERE UserID='$UserID'");
			list($EmailChanges) = $DB->next_record();
		}
?>
	<div class="box">
		<div class="head colhead_dark">History</div>
		<ul class="stats nobullet">
<?	if (check_perms('users_view_email',$Class)) { ?>
<li>Emails: <?=number_format($EmailChanges)?> [<a href="userhistory.php?action=email2&amp;userid=<?=$UserID?>">View</a>]&nbsp;[<a href="userhistory.php?action=email&amp;userid=<?=$UserID?>">Legacy view</a>]</li>
<?
	}
	if (check_perms('users_view_ips',$Class)) {
?>
	<li>IPs: <?=number_format($IPChanges)?> [<a href="userhistory.php?action=ips&amp;userid=<?=$UserID?>">View</a>]&nbsp;[<a href="userhistory.php?action=ips&amp;userid=<?=$UserID?>&amp;usersonly=1">View Users</a>]</li>
	<li>ASNs: <?=number_format((int)$ASNChanges)?> [<a href="userhistory.php?action=asn&amp;userid=<?=$UserID?>">View</a>]&nbsp;[<a href="userhistory.php?action=asn&amp;userid=<?=$UserID?>&amp;usersonly=1">View 
Users</a>]</li>
	<li>Countries: <?=number_format((int)$CountryChanges)?> [<a href="userhistory.php?action=country&amp;userid=<?=$UserID?>">View</a>]&nbsp;[<a 
href="userhistory.php?action=country&amp;userid=<?=$UserID?>&amp;usersonly=1">View Users</a>]</li>
	<li>CIDRs: <?=number_format((int)$CidrChanges)?> [<a href="userhistory.php?action=cidr&amp;userid=<?=$UserID?>">View</a>]&nbsp;[<a href="userhistory.php?action=cidr&amp;userid=<?=$UserID?>&amp;usersonly=1">View 
Users</a>]</li>
<?
	}
	if (check_perms('users_mod', $Class)) {
?>
			<li>Passwords: <?=number_format($PasswordChanges)?> [<a href="userhistory.php?action=passwords&amp;userid=<?=$UserID?>">View</a>]</li>
			<li>Stats: N/A [<a href="userhistory.php?action=stats&amp;userid=<?=$UserID?>">View</a>]</li>
<?
			
	}
?>
		</ul>
	</div>
<?	} ?>
		<div class="box">
			<div class="head colhead_dark">Personal</div>
			<ul class="stats nobullet">
				<li>Class: <?=$ClassLevels[$Class]['Name']?></li>
<?
// An easy way for people to measure the paranoia of a user, for e.g. contest eligibility
if($ParanoiaLevel == 0) {
	$ParanoiaLevelText = 'Off';
} elseif($ParanoiaLevel == 1) {
	$ParanoiaLevelText = 'Very Low';
} elseif($ParanoiaLevel <= 5) {
	$ParanoiaLevelText = 'Low';
} elseif($ParanoiaLevel <= 20) {
	$ParanoiaLevelText = 'High';
} else {
	$ParanoiaLevelText = 'Very high';
}
?>
				<li>Paranoia level: <span title="<?=$ParanoiaLevel?>"><?=$ParanoiaLevelText?></span></li>
<?	if (check_perms('users_view_email',$Class) || $OwnProfile) { ?>
				<li>Email: <a href="mailto:<?=display_str($Email)?>"><?=display_str($Email)?></a>
<?		if (check_perms('users_view_email',$Class)) { ?>
					[<a href="user.php?action=search&amp;email_history=on&amp;email=<?=display_str($Email)?>" title="Search">S</a>]
<?		} ?>
				</li>
<?	}

if (check_perms('users_view_ips',$Class)) {
?>
				<li>IP: <?=display_ip($IP)?></li>
				<li>Host: <?=get_host($IP)?></li>
<?
}

if (check_perms('users_view_invites')) {
	if (!$InviterID) {
		$Invited="<i>Nobody</i>";
	} else {
		$Invited='<a href="user.php?id='.$InviterID.'">'.$InviterName.'</a>';
	}
	
?>
				<li>Invited By: <?=$Invited?></li>
				<li>Invites: <? 
				$DB->query("SELECT count(InviterID) FROM invites WHERE InviterID = '$UserID'");
				list($Pending) = $DB->next_record();
				if($DisableInvites) { 
					echo 'X'; 
				} else { 
					echo number_format($Invites); 
				} 
				echo " (".$Pending.")"
				?></li>
<?
}

if (!isset($SupportFor)) {
	$DB->query("SELECT SupportFor FROM users_info WHERE UserID = ".$LoggedUser['ID']);
	list($SupportFor) = $DB->next_record();
}
?>
			</ul>
		</div>
	</div>
	<div class="main_column">
<?
if ($RatioWatchEnds!='0000-00-00 00:00:00'
		&& (time() < strtotime($RatioWatchEnds))
		&& ($Downloaded*$RequiredRatio)>$Uploaded
		) {
?>
		<div class="box">
			<div class="head">Ratio watch</div>
			<div class="pad">This user is currently on ratio watch, and must upload <?=get_size(($Downloaded*$RequiredRatio)-$Uploaded)?> in the next <?=time_diff($RatioWatchEnds)?>, or their leeching privileges will be revoked. Amount downloaded while on ratio watch: <?=get_size($Downloaded-$RatioWatchDownload)?></div>
		</div>
<? } ?>
		<div class="box">
			<div class="head">
				<span style="float:left;">Profile<? if ($CustomTitle) { echo " - ".$Text->full_format(html_entity_decode($DisplayCustomTitle)); } ?></span>
				<span style="float:right;"><?=$Badges?></span>&nbsp;
			</div>
			<div class="pad">
<? if (!$Info) { ?>
				This profile is currently empty.
<?
} else {
	echo $Text->full_format($Info);
}

?>
			</div>
		</div>
<?
if ($Snatched > 4 && check_paranoia_here('snatched')) {
	$RecentSnatches = $Cache->get_value('recent_snatches_'.$UserID);
	if(!is_array($RecentSnatches)){
		$DB->query("SELECT
		g.ID,
		g.Name,
		g.WikiImage
		FROM xbt_snatched AS s
		INNER JOIN torrents AS t ON t.ID=s.fid
		INNER JOIN torrents_group AS g ON t.GroupID=g.ID
		WHERE s.uid='$UserID'
		AND g.WikiImage <> ''
		GROUP BY g.ID
		ORDER BY s.tstamp DESC
		LIMIT 5");
		$RecentSnatches = $DB->to_array();
		
		$Artists = get_artists($DB->collect('ID'));
		foreach($RecentSnatches as $Key => $SnatchInfo) {
			$RecentSnatches[$Key]['Artist'] = display_artists($Artists[$SnatchInfo['ID']], false, true);
		}
		$Cache->cache_value('recent_snatches_'.$UserID, $RecentSnatches, 0); //inf cache
	}
?>
	<table class="recent" cellpadding="0" cellspacing="0" border="0">
		<tr class="colhead">
			<td colspan="5">Recent Snatches</td>
		<tr>
		<tr>
<?		
		foreach($RecentSnatches as $RS) { ?>
			<td>
				<a href="torrents.php?id=<?=$RS['ID']?>" title="<?=display_str($RS['Artist'])?><?=display_str($RS['Name'])?>"><img src="<?=$RS['WikiImage']?>" alt="<?=display_str($RS['Artist'])?><?=display_str($RS['Name'])?>" width="107" /></a>
			</td>
<?		} ?>
		</tr>
	</table>
<?
}

if(!isset($Uploads)) { $Uploads = 0; }
if ($Uploads > 4 && check_paranoia_here('uploads')) {
	$RecentUploads = $Cache->get_value('recent_uploads_'.$UserID);
	if(!is_array($RecentUploads)){
		$DB->query("SELECT 
		g.ID,
		g.Name,
		g.WikiImage
		FROM torrents_group AS g
		INNER JOIN torrents AS t ON t.GroupID=g.ID
		WHERE t.UserID='$UserID'
		AND g.WikiImage <> ''
		GROUP BY g.ID
		ORDER BY t.Time DESC
		LIMIT 5");
		$RecentUploads = $DB->to_array();
		$Artists = get_artists($DB->collect('ID'));
		foreach($RecentUploads as $Key => $UploadInfo) {
			$RecentUploads[$Key]['Artist'] = display_artists($Artists[$UploadInfo['ID']], false, true);
		}
		$Cache->cache_value('recent_uploads_'.$UserID, $RecentUploads, 0); //inf cache
	}
?>
	<table class="recent" cellpadding="0" cellspacing="0" border="0">
		<tr class="colhead">
			<td colspan="5">Recent Uploads</td>
		<tr>
<?		foreach($RecentUploads as $RU) { ?>
			<td>
				<a href="torrents.php?id=<?=$RU['ID']?>" title="<?=$RU['Artist']?><?=$RU['Name']?>"><img src="<?=$RU['WikiImage']?>" alt="<?=$RU['Artist']?><?=$RU['Name']?>" width="107" /></a>
			</td>
<?		} ?>
		</tr>
	</table>
<?
}
//calls class_invite_tree
if ($Invited > 0) {
	include(SERVER_ROOT.'/classes/class_invite_tree.php');
	$Tree = new INVITE_TREE($UserID, array('visible'=>false));
?>
		<div class="box">
			<div class="head">Invite Tree <a href="#" onclick="$('#invitetree').toggle();return false;">(View)</a></div>
			<div id="invitetree" class="hidden">
				<? $Tree->make_tree(); ?>
			</div>
		</div>
<?
}


if (check_perms('users_mod', $Class)) { ?>
		<form id="form" action="user.php" method="post">
		<input type="hidden" name="action" value="moderate" />
		<input type="hidden" name="userid" value="<?=$UserID?>" />
		<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />

		<div class="box">
			<div class="head">Staff Notes <a href="#" name="admincommentbutton" onclick="ChangeTo('text'); return false;">(Edit)</a></div>
			<div class="pad">
				<div id="admincommentlinks" class="AdminComment box" style="width:98%;"><?=$Text->full_format($AdminComment)?></div>
				<textarea id="admincomment" onkeyup="resize('admincomment');" class="AdminComment hidden" name="AdminComment" cols="65" rows="26" style="width:98%;"><?=display_str($AdminComment)?></textarea>
				<a href="#" name="admincommentbutton" onclick="ChangeTo('text'); return false;">Toggle Edit</a>
				<script type="text/javascript">
					resize('admincomment');
				</script>
			</div>
		</div>

		<table>
			<tr>
				<td class="colhead" colspan="2">User Info</td>
			</tr>
<?	if (check_perms('users_edit_usernames', $Class)) { ?>
			<tr>
				<td class="label">Username:</td>
				<td><input type="text" size="20" name="Username" value="<?=display_str($Username)?>" /></td>
			</tr>
<?
	}

	if (check_perms('users_edit_titles')) {
?>
			<tr>
				<td class="label">CustomTitle:</td>
				<td><input type="text" size="50" name="Title" value="<?=display_str($CustomTitle)?>" /></td>
			</tr>
<?
	}

	if (check_perms('users_promote_below', $Class) || check_perms('users_promote_to', $Class-1)) {
?>
			<tr>
				<td class="label">Class:</td>
				<td>
					<select name="Class">
<?
		foreach ($ClassLevels as $CurClass) {
			if (check_perms('users_promote_below', $Class) && $CurClass['ID']>=$LoggedUser['Class']) { break; }
			if ($CurClass['ID']>$LoggedUser['Class']) { break; }
			if ($Class===$CurClass['Level']) { $Selected='selected="selected"'; } else { $Selected=""; }
?>
						<option value="<?=$CurClass['ID']?>" <?=$Selected?>><?=$CurClass['Name'].' ('.$CurClass['Level'].')'?></option>
<?		} ?>
					</select>
				</td>
			</tr>
<?
	}

	if (check_perms('users_give_donor')) {
?>
			<tr>
				<td class="label">Donor:</td>
				<td><input type="checkbox" name="Donor" <? if ($Donor == 1) { ?>checked="checked" <? } ?> /></td>
			</tr>
<?
	}
	if (check_perms('users_promote_below') || check_perms('users_promote_to')) {
?>
			<tr>
				<td class="label">Artist:</td>
				<td><input type="checkbox" name="Artist" <? if ($Artist == 1) { ?>checked="checked" <? } ?> /></td>
			</tr>
<?
	}
	if (check_perms('users_make_invisible')) {
?>
			<tr>
				<td class="label">Visible:</td>
				<td><input type="checkbox" name="Visible" <? if ($Visible == 1) { ?>checked="checked" <? } ?> /></td>
			</tr>
<?
	}

	if (check_perms('users_edit_ratio',$Class) || (check_perms('users_edit_own_ratio') && $UserID == $LoggedUser['ID'])) {
?>
			<tr>
				<td class="label">Uploaded:</td>
				<td>
					<input type="hidden" name="OldUploaded" value="<?=$Uploaded?>" />
					<input type="text" size="20" name="Uploaded" value="<?=$Uploaded?>" />
				</td>
			</tr>
			<tr>
				<td class="label">Downloaded:</td>
				<td>
					<input type="hidden" name="OldDownloaded" value="<?=$Downloaded?>" />
					<input type="text" size="20" name="Downloaded" value="<?=$Downloaded?>" />
				</td>
			</tr>
			<tr>
				<td class="label">Merge Stats <strong>From:</strong></td>
				<td>
					<input type="text" size="40" name="MergeStatsFrom" />
				</td>
			</tr>
<?
	}

	if (check_perms('users_edit_invites')) {
?>
			<tr>
				<td class="label">Invites:</td>
				<td><input type="text" size="5" name="Invites" value="<?=$Invites?>" /></td>
			</tr>
<?
	}

	if (check_perms('admin_manage_fls') || (check_perms('users_mod') && $OwnProfile)) {
?>
			<tr>
				<td class="label">First Line Support:</td>
				<td><input type="text" size="50" name="SupportFor" value="<?=display_str($SupportFor)?>" /></td>
			</tr>
<?
	}

	if (check_perms('users_edit_reset_keys')) {
?>
			<tr>
				<td class="label">Reset:</td>
				<td>
					<input type="checkbox" name="ResetRatioWatch" id="ResetRatioWatch" /> <label for="ResetRatioWatch">Ratio Watch</label> |
					<input type="checkbox" name="ResetPasskey" id="ResetPasskey" /> <label for="ResetPasskey">Passkey</label> |
					<input type="checkbox" name="ResetAuthkey" id="ResetAuthkey" /> <label for="ResetAuthkey">Authkey</label> |
					<input type="checkbox" name="ResetIPHistory" id="ResetIPHistory" /> <label for="ResetIPHistory">IP History</label> |
					<input type="checkbox" name="ResetEmailHistory" id="ResetEmailHistory" /> <label for="ResetEmailHistory">Email History</label>
					<br />
					<input type="checkbox" name="ResetSnatchList" id="ResetSnatchList" /> <label for="ResetSnatchList">Snatch List</label> | 
					<input type="checkbox" name="ResetDownloadList" id="ResetDownloadList" /> <label for="ResetDownloadList">Download List</label>
				</td>
			</tr>
<?
	}

	if (check_perms('users_mod')) {
?>
		<tr>
			<td class="label">Reset all EAC v0.95 Logs To:</td>
			<td>
				<select name="095logs">
					<option value=""></option>
					<option value="99">99</option>
					<option value="100">100</option>
				</select>
			</td>
		</tr>
<?	}

	if (check_perms('users_edit_password')) {
?>
			<tr>
				<td class="label">New Password:</td>
				<td>
					<input type="text" size="30" id="change_password" name="ChangePassword" />
				</td>
			</tr>
<?	} ?>
		</table><br />

<?	if (check_perms('users_warn')) { ?>
		<table>
			<tr class="colhead">
				<td colspan="2">Warn User</td>
			</tr>
			<tr>
				<td class="label">Warned:</td>
				<td>
					<input type="checkbox" name="Warned" <? if ($Warned != '0000-00-00 00:00:00') { ?>checked="checked"<? } ?> />
				</td>
			</tr>
<?		if ($Warned=='0000-00-00 00:00:00') { // user is not warned ?>
			<tr>
				<td class="label">Expiration:</td>
				<td>
					<select name="WarnLength">
						<option value="">---</option>
						<option value="1"> 1 Week</option>
						<option value="2"> 2 Weeks</option>
						<option value="4"> 4 Weeks</option>
						<option value="8"> 8 Weeks</option>
					</select>
				</td>
			</tr>
<?		} else { // user is warned ?>
			<tr>
				<td class="label">Extension:</td>
				<td>
					<select name="ExtendWarning">
						<option>---</option>
						<option value="1"> 1 Week</option>
						<option value="2"> 2 Weeks</option>
						<option value="4"> 4 Weeks</option>
						<option value="8"> 8 Weeks</option>
					</select>
				</td>
			</tr>
<?		} ?>
			<tr>
				<td class="label">Reason:</td>
				<td>
					<input type="text" size="60" name="WarnReason" />
				</td>
			</tr>
<?	} ?>
		</table><br />
		<table>
			<tr class="colhead"><td colspan="2">User Privileges</td></tr>
<?	if (check_perms('users_disable_posts') || check_perms('users_disable_any')) {
		$DB->query("SELECT DISTINCT Email, IP FROM users_history_emails WHERE UserID = ".$UserID." ORDER BY Time ASC");
		$Emails = $DB->to_array();
?>
			<tr>
				<td class="label">Disable:</td>
				<td>
					<input type="checkbox" name="DisablePosting" id="DisablePosting"<? if ($DisablePosting==1) { ?>checked="checked"<? } ?> /> <label for="DisablePosting">Posting</label>
<?		if (check_perms('users_disable_any')) { ?>  |
					<input type="checkbox" name="DisableAvatar" id="DisableAvatar"<? if ($DisableAvatar==1) { ?>checked="checked"<? } ?> /> <label for="DisableAvatar">Avatar</label> |
					<input type="checkbox" name="DisableInvites" id="DisableInvites"<? if ($DisableInvites==1) { ?>checked="checked"<? } ?> /> <label for="DisableInvites">Invites</label> |
					
					<input type="checkbox" name="DisableForums" id="DisableForums"<? if ($DisableForums==1) { ?>checked="checked"<? } ?> /> <label for="DisableForums">Forums</label> |
					<input type="checkbox" name="DisableTagging" id="DisableTagging"<? if ($DisableTagging==1) { ?>checked="checked"<? } ?> /> <label for="DisableTagging">Tagging</label> |
					<input type="checkbox" name="DisableRequests" id="DisableRequests"<? if ($DisableRequests==1) { ?>checked="checked"<? } ?> /> <label for="DisableRequests">Requests</label>
					<br />
					 <input type="checkbox" name="DisableUpload" id="DisableUpload"<? if ($DisableUpload==1) { ?>checked="checked"<? } ?> /> <label for="DisableUpload">Upload</label> |
					<input type="checkbox" name="DisableWiki" id="DisableWiki"<? if ($DisableWiki==1) { ?>checked="checked"<? } ?> /> <label for="DisableWiki">Wiki</label> |
					<input type="checkbox" name="DisableLeech" id="DisableLeech"<? if ($DisableLeech==0) { ?>checked="checked"<? } ?> /> <label for="DisableLeech">Leech</label> |
					<input type="checkbox" name="DisablePM" id="DisablePM"<? if ($DisablePM==1) { ?>checked="checked"<? } ?> /> <label for="DisablePM">PM</label> |
					<input type="checkbox" name="DisableIRC" id="DisableIRC"<? if ($DisableIRC==1) { ?>checked="checked"<? } ?> /> <label for="DisableIRC">IRC</label>
				</td>
			</tr>
			<tr>
				<td class="label">Hacked:</td>
				<td>
					<input type="checkbox" name="SendHackedMail" id="SendHackedMail" /> <label for="SendHackedMail">Send hacked account email</label> to 
					<select name="HackedEmail">
<?
			foreach($Emails as $Email) {
				list($Address, $IP) = $Email;
?>
						<option value="<?=display_str($Address)?>"><?=display_str($Address)?> - <?=display_str($IP)?></option>
<?			} ?>
					</select>
				</td>
			</tr>

<?		} ?>
<?
	}

	if (check_perms('users_disable_any')) {
?>
			<tr>
				<td class="label">Account:</td>
				<td>
					<select name="UserStatus">
						<option value="0" <? if ($Enabled=='0') { ?>selected="selected"<? } ?>>Unconfirmed</option>
						<option value="1" <? if ($Enabled=='1') { ?>selected="selected"<? } ?>>Enabled</option>
						<option value="2" <? if ($Enabled=='2') { ?>selected="selected"<? } ?>>Disabled</option>
<?		if (check_perms('users_delete_users')) { ?>
						<optgroup label="-- WARNING --"></optgroup>
						<option value="delete">Delete Account</option>
<?		} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">User Reason:</td>
				<td>
					<input type="text" size="60" name="UserReason" />
				</td>
			</tr>
			<tr>
				<td class="label">Restricted Forums (comma-delimited):</td>
				<td>
						<input type="text" size="60" name="RestrictedForums" value="<?=display_str($RestrictedForums)?>" />
				</td>
			</tr>
			<tr>
				<td class="label">Extra Forums (comma-delimited):</td>
				<td>
						<input type="text" size="60" name="PermittedForums" value="<?=display_str($PermittedForums)?>" />
				</td>
			</tr>

<?	} ?>
		</table><br />
<?	if(check_perms('users_logout')) { ?>
		<table>
			<tr class="colhead"><td colspan="2">Session</td></tr>
			<tr>
				<td class="label">Reset session:</td>
				<td><input type="checkbox" name="ResetSession" id="ResetSession" /></td>
			</tr>
			<tr>
				<td class="label">Log out:</td>
				<td><input type="checkbox" name="LogOut" id="LogOut" /></td>
			</tr>

		</table>
<?	} ?>
		<table>
			<tr class="colhead"><td colspan="2">Submit</td></tr>
			<tr>
				<td class="label">Reason:</td>
				<td>
					<textarea rows="1" cols="50" name="Reason" id="Reason" onkeyup="resize('Reason');"></textarea>
				</td>
			</tr>

			<tr>
				<td align="right" colspan="2">
					<input type="submit" value="Save Changes" />
				</td>
			</tr>
		</table>
		</form>
<? } ?>
	</div>
</div>
<? show_footer(); ?>
