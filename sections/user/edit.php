<?php
$UserID = $_REQUEST['userid'];
if(!is_number($UserID)){
	error(404);
}

$DB->query("SELECT 
			m.Username,
			m.Email,
			i.Info,
			i.Avatar,
			i.Country,
			i.Timezone,
			i.StyleID,
			i.StyleURL,
			i.SiteOptions,
			p.Level AS Class
			FROM users_main AS m
			JOIN users_info AS i ON i.UserID = m.ID
			LEFT JOIN permissions AS p ON p.ID=m.PermissionID
			WHERE m.ID = '".db_string($UserID)."'");
list($Username,$Email,$Info,$Avatar,$Country,$Timezone,$StyleID,$StyleURL,$SiteOptions,$Class)=$DB->next_record(MYSQLI_NUM, array(3,9));


if($UserID != $LoggedUser['ID'] && !check_perms('users_edit_profiles', $Class)) {
	error(403);
}

function checked($Checked) {
	return $Checked ? 'checked="checked"' : '';
}


if ($SiteOptions) { 
	$SiteOptions = unserialize($SiteOptions); 
} else { 
	$SiteOptions = array();
}

show_header($Username.' > Settings','user,validate,jquery,jquery.dd');
echo $Val->GenerateJS('userform');
?>
<div class="thin">
	<h2><?=format_username($UserID,$Username)?> &gt; Settings</h2>
	<form id="userform" name="userform" action="" method="post" onsubmit="return formVal();" autocomplete="off">
		<div>
			<input type="hidden" name="action" value="takeedit" />
			<input type="hidden" name="userid" value="<?=$UserID?>" />
			<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
		</div>
		<table cellpadding='6' cellspacing='1' border='0' width='100%' class='border'>
			<tr class="colhead_dark">
				<td colspan="2">
					<strong>Site preferences</strong>
				</td>
			</tr>
<?

?>
			<tr>
				<td class="label"><strong>Stylesheet</strong></td>
				<td>
					<select name="stylesheet" id="stylesheet">
<? foreach($Stylesheets as $Style) { ?>
						<option value="<?=$Style['ID']?>"<? if ($Style['ID'] == $StyleID) { ?>selected="selected"<? } ?>><?=$Style['ProperName']?></option>
<? } ?>
					</select>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Or -&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					External CSS: <input type="text" size="40" name="styleurl" id="styleurl" value="<?=display_str($StyleURL)?>" />
				</td>
			</tr>
			<tr class="colhead_dark">
				<td colspan="2">
					<strong>User info</strong>
				</td>
			</tr>
			
			<tr>
				<td class="label"><strong>Avatar URL</strong></td>
				<td>
					<input type="text" size="50" name="avatar" id="avatar" value="<?=display_str($Avatar)?>" />
					<p class="min_padding">Width should be 150 pixels (will be resized if necessary)</p>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Email</strong></td>
				<td><input type="text" size="50" name="email" id="email" value="<?=display_str($Email)?>" />
					<p class="min_padding">If changing this field you must enter your current password in the "Current password" field before saving your changes.</p>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Info</strong></td>
				<td><textarea name="info" cols="50" rows="8"><?=display_str($Info)?></textarea></td>
			</tr>
			<tr class="colhead_dark">
				<td colspan="2">
					<strong>Change password</strong>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Current password</strong></td>
				<td><input type="password" size="40" name="cur_pass" id="cur_pass" value="" /></td>
			</tr>
			<tr>
				<td class="label"><strong>New password</strong></td>
				<td><input type="password" size="40" name="new_pass_1" id="new_pass_1" value="" /></td>
			</tr>
			<tr>
				<td class="label"><strong>Re-type new password</strong></td>
				<td><input type="password" size="40" name="new_pass_2" id="new_pass_2" value="" /></td>
			</tr>
			<tr>
				<td colspan="2" class="right">
					<input type="submit" value="Save Profile" />
				</td>
			</tr>
		</table>
	</form>
</div>
<?
show_footer();
?>
