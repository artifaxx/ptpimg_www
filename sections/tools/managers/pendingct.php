<?
if((isset($_GET['flag']) && !empty($_GET['flag'])) && $_GET['flag']=="update") {
	authorize();
	
	if(isset($_GET['eid']) && isset($_GET['val']) && is_number($_GET['eid']) && is_numeric($_GET['val'])) {
		$DB->query("UPDATE bp_pendingct SET StaffID=".db_string($LoggedUser['ID']).", TimeChange=NOW(), Approved='".db_string($_GET['val'])."' WHERE ID=".db_string($_GET['eid'])." LIMIT 1");
		if($_GET['val']==1) {
			$DB->query("SELECT UserID, Title FROM bp_pendingct WHERE ID=".db_string($_GET['eid']));
			list($UserID,$Title)=$DB->next_record();
			$DB->query("UPDATE users_main SET Title='".db_string($Title)."' WHERE ID=".db_string($UserID));
			$Cache->delete_value('user_info_'.$UserID);
		} else if ($_GET['val']==-1) {
?>
		<input id="vx_<?=$_GET['eid']?>" type="button" value="Refund" onclick="v2_callback(<?=$_GET['eid']?>)" />
<?
		}
		echo "Just now by <a href='user.php?id='".$LoggedUser['ID'].">".$LoggedUser['Username']."</a>";
	}
	
	die();
}
if((isset($_GET['flag']) && !empty($_GET['flag'])) && $_GET['flag']=="refund") {
	authorize();
	if(isset($_GET['eid']) && is_number($_GET['eid'])) {
		$DB->query("SELECT UserID, Refunded, RefundAmount FROM bp_pendingct WHERE ID=".db_string($_GET['eid']));
		list($UserID, $Refunded, $RefundAmount)=$DB->next_record();
		if($Refunded) die("done"); // They've already been refunded. Do nothing.
		$DB->query("UPDATE users_bp SET Points=Points+$RefundAmount WHERE UserID=".$UserID);
		$DB->query("UPDATE bp_pendingct SET Refunded='1' WHERE ID=".db_string($_GET['eid']));
		bp_getpoints($UserID,1);
	}
	echo "ok";
	
	die();
}

show_header();
$DB->query("SELECT 
			pt.ID,
			pt.UserID,
			pt.Title,
			pt.Approved,
			pt.StaffID,
			pt.Time,
			pt.TimeChange,
			um1.Username as Username,
			um2.Username as StaffUsername,
			um1.Title AS CurrentTitle,
			um1.PermissionID,
			um1.Enabled,
			ui1.Donor,
			ui1.Warned
			FROM bp_pendingct AS pt
			LEFT JOIN users_main AS um1 ON um1.ID=pt.UserID
			LEFT JOIN users_main AS um2 ON um2.ID=pt.StaffID
			LEFT JOIN users_info AS ui1 ON ui1.UserID=pt.UserID
			ORDER BY pt.ID");
$Pending = $DB->to_array();

include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

function status($c) {
	switch($c) {
		case '-1':
			return "Denied";
		break;
		case '0':
			return "Pending";
		break;
		case '1':
			return "Approved";
	}
}
?>

<script type="text/javascript">

function v2_callback(eid) {
	val=$('#vx_'+eid).raw().value;
	ajax.get('tools.php?action=pendingct&flag=refund&eid='+eid+'&auth='+authkey, function(response) {
		$('#vx_'+eid).raw().disabled=true;
	});
}

function v4_callback(eid) {
	val=$('#vsel_'+eid).raw().value;
	ajax.get('tools.php?action=pendingct&flag=update&eid='+eid+'&val='+val+'&auth='+authkey, function(response) {
		$('#vresp_'+eid).raw().innerHTML=response;
	});
}
</script>

<div id="content">
	<h2>Pending Custom Titles</h2>
	<div class="box">
		<table width="100%">
			<tr class="colhead">
				<td>User</td>
				<td>Time</td>
				<td>Title</td>
				<td>Status</td>
				<td>Approve/Deny</td>
				<td>Last Movement</td>
			</tr>
			<? while(list($Key,list($EntryID,$UserID,$Title,$Approved,$StaffID,$Time,$TimeChange,$Username,$StaffUsername,$CurrentTitle,$PermissionID,$Enabled,$Donor,$Warned))=each($Pending)) { ?>
			<tr>
				<td><?=format_username($UserID, $Username, $Donor, $Warned, $Enabled, $PermissionID)?></td>
				<td><?=time_diff($Time)?></td>
				<td><?=$Text->full_format($Title)?></td>
				<td><?=status($Approved)?></td>
				<td><select onchange="v4_callback(<?=$EntryID?>)" style="width:100%" name="vsel_<?=$EntryID?>" id="vsel_<?=$EntryID?>"><option value="-1">Denied</option <?=($Approved==-1)?'selected':''?>><option value="0" <?=(!$Approved)?'selected':''?>>Pending</option><option value="1" <?=($Approved==1)?'selected':''?>>Approve</option></select></td>
				<td id="vresp_<?=$EntryID?>"><?=time_diff($TimeChange)?><?=($StaffID)?' by <a href="user.php?id='.$StaffID.'">'.$StaffUsername.'</a>':''?></td>
			</tr>
			<? } ?>
		</table>
	</div>
</div>
<?
show_footer();
?>