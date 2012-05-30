<?
if(!check_perms('site_recommend_own') && !check_perms('site_manage_recommendations')){
	error(403);
}
show_header('Recommendations');
	$DB->query("SELECT 
		tr.GroupID,
		tr.UserID,
		u.Username,
		tr.Time,
		tr.Expires,
		tr.Active,
		tg.Name,
		tr.Bonus
		FROM torrents_recommended AS tr
		JOIN torrents_group AS tg ON tg.ID=tr.GroupID
		LEFT JOIN users_main AS u ON u.ID=tr.UserID
		ORDER BY tr.Time DESC
		LIMIT 25
		");
?>
<div class="thin">
	<div class="box" id="recommended">
		<div class="head colhead_dark"><strong>Recommendations</strong></div>
<?		/*if(is_array($DB->collect('UserID'))) {
			if(!in_array($LoggedUser['ID'], $DB->collect('UserID'))){ */?>
		<form action="tools.php" method="post" class="pad">
			<table cellpadding="6" cellspacing="1" border="0" class="border" width="100%">
				<tr>
					<td class="label"><strong>Torrent Group URL:</strong></td>
					<td>
						<input type="text" name="url" size="50" />
					</td>
				</tr>
				<tr>
					<td class="label"><strong>Review:</strong></td>
					<td>
						<textarea name="review" rows="10" style="width: 98%"></textarea>
						<strong>This will be shown below the name on the front page.</strong>
					</td>
				</tr>
				<tr>
					<td class="label"><strong>Length:</strong></td>
					<td>
						<select name="length">
							<option value="24">24 hours / 1 day</option>
							<option value="48">48 hours / 2 days</option>
							<option value="72">72 hours / 3 days</option>
							<option value="96">96 hours / 4 days</option>
							<option value="120">120 hours / 5 days</option>
							<option value="144">144 hours / 6 days</option>
							<option value="168">168 hours / 7 days</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label"><strong>Point Bonus</strong></td>
					<td>
						<select name="bonus">
							<option value="1">No bonus (1.0; neutral)</option>
							<option value="1.25">1.25 (125%)</option>
							<option value="1.5">1.5 (150%)</option>
							<option value="2.0">2.0 (200%)</option>
						</select>
						<br />
						Be careful with the bonuses. These will stack with any bonuses the user already has.
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="hidden" name="action" value="recommend_add" />
						<input type="submit" value="Add recommendation" />
					</td>
				</tr>
			</table>
		</form>
<?		/*}
} */?>
</div><br>
	<table width="100%">
		<tr class="colhead">
			<td>Time Added</td>
			<td>User</td>
			<td>Name</td>
			<td>Active</td>
			<td>Bonus</td>
<? if(check_perms('site_manage_recommendations') || $UserID == $LoggedUser['ID']){ ?><td>Delete</td><? } ?>
		</tr>
		

<?

function findClosestTime() {
$CurTime=date('i');
$TimeList=array(0,15,30,45,60);
$NextRun=0;
	while (list($Key,$Time)=each($TimeList)) {
		if (($Time-$CurTime) > 0) {
			$NextRun = $Time-$CurTime;
			break;
		}
	}
return $NextRun . " minute(s)";
}

	while(list($GroupID, $UserID, $Username, $Time, $Expires, $Active, $GroupName, $Bonus)=$DB->next_record()) {
?>
		<tr>
<?		if ($Time) { ?>
				<td><i><?=$Time?></i></	>
<?		} ?>
				<td><b><?=format_username($UserID, $Username)?></b></td>
		<td>
<?
                        $Artists = get_artist($GroupID);

                        if($Artists) {
                                echo display_artists($Artists, true);
                        }
?>
				<a href="torrents.php?id=<?=$GroupID?>"><?=$GroupName?></a>
		</td>
<?		if($Active){ ?>
				<td>Yes, expires in: <?=if_empty(time_diff(strtotime($Expires)), 'Expired')?></td>
<?		} else { ?>
				<td>No, will be active in: <?=findClosestTime()?></td>
<?		} ?>
				<td><?=$Bonus?></td>
<?		if(check_perms('site_manage_recommendations') || $UserID == $LoggedUser['ID']){ ?>
				<td><a href="tools.php?action=recommend_alter&amp;groupid=<?=$GroupID?>">[Delete]</a></td>
<?		} ?> 
		</tr>
<?	} ?>
	</table>
</div>
<?
show_footer();
?>
