<?
function time_df($TS1,$TS2,$Levels=2,$Span=true) {
	$TimeStamp1 = strtotime($TS1);
	$TimeStamp2 = strtotime($TS2);
	$Time = $TimeStamp1-$TimeStamp2;

        //If the time is negative, then we know that it expires in the future
        if($Time < 0) {
                $Time = -$Time;
                $HideAgo = true;
        }
		
        $Years=floor($Time/31556926); // seconds in a year
        $Remain = $Time - $Years*31556926;

        $Months = floor($Remain/2629744); // seconds in a month
        $Remain = $Remain - $Months*2629744;

        $Weeks = floor($Remain/604800); // seconds in a week
        $Remain = $Remain - $Weeks*604800;

        $Days = floor($Remain/86400); // seconds in a day
        $Remain = $Remain - $Days*86400;

        $Hours=floor($Remain/3600);
        $Remain = $Remain - $Hours*3600;

        $Minutes=floor($Remain/60);
        $Remain = $Remain - $Minutes*60;

        $Seconds=$Remain;

        $Return = '';

        if ($Years>0 && $Levels>0) {
                if ($Years>1) {
                        $Return .= $Years.' years';
                } else {
                        $Return .= $Years.' year';
                }
                $Levels--;
        }

        if ($Months>0 && $Levels>0) {
                if ($Return!='') {
                        $Return.=', ';
                }
                if ($Months>1) {
                        $Return.=$Months.' months';
                } else {
                        $Return.=$Months.' month';
                }
                $Levels--;
        }

        if ($Weeks>0 && $Levels>0) {
                if ($Return!="") {
                        $Return.=', ';
                }
                if ($Weeks>1) {
                        $Return.=$Weeks.' weeks';
                } else {
                        $Return.=$Weeks.' week';
                }
                $Levels--;
        }

        if ($Days>0 && $Levels>0) {
                if ($Return!='') {
                        $Return.=', ';
                }
                if ($Days>1) {
                        $Return.=$Days.' days';
                } else {
                        $Return.=$Days.' day';
                }
                $Levels--;
        }

        if ($Hours>0 && $Levels>0) {
                if ($Return!='') {
                        $Return.=', ';
                }
                if ($Hours>1) {
                        $Return.=$Hours.' hours';
                } else {
                        $Return.=$Hours.' hour';
                }
                $Levels--;
        }

        if ($Minutes>0 && $Levels>0) {
                if ($Return!='') {
                        $Return.=' and ';
                }
                if ($Minutes>1) {
                        $Return.=$Minutes.' mins';
                } else {
                        $Return.=$Minutes.' min';
                }
                $Levels--;
        }

        if($Return == '') {
                $Return = 'Just now';
        } elseif (!isset($HideAgo)) {
                $Return .= ' ago';
        }

        if ($Span) {
                return '<span class="time" title="'.date('M d Y, H:i', strtotime(time_plus($Time))).'">'.$Return.'</span>';
        } else {
                return $Return;
        }
}

if(!isset($_GET['v']) || empty($_GET['v'])) { ?>
<a href="tools.php?action=public_sandbox&v=snatched">Snatched?</a>
<a href="tools.php?action=public_sandbox&v=promo">Promo</a>
<? die(); }

if($_GET['v']=="snatched") {
	$DB->query("SELECT 
				t.ID,
				tg.Name,
				t.GroupID,
				t.UserID,
				xs.uid
				FROM torrents AS t
				LEFT JOIN torrents_group AS tg ON tg.ID=t.GroupID
				LEFT JOIN xbt_snatched AS xs ON xs.fid=t.ID AND xs.uid=".db_string($LoggedUser['ID'])."
				WHERE xs.uid is null AND t.UserID!=".db_string($LoggedUser['ID']));
	while(list($ID,$Name,$GroupID,$UserID)=$DB->next_record()) {
	//http://musiceye.tv/torrents.php?action=download&id=421&&torrent_pass=
		echo "<a href='torrents.php?id=".$GroupID."&torrentid=".$ID."'>$Name</a> [<a href='torrents.php?action=download&id=$ID&auth=".$LoggedUser['AuthKey']."&torrent_pass=".$LoggedUser['torrent_pass']."'>DL</a>]<br />";
	}
} else if($_GET['v']=="promo") {
	$DB->query("SELECT COUNT(ID) FROM torrents WHERE UserID='".db_string($LoggedUser['ID'])."'");
	list($Uploads) = $DB->next_record();
	
	$DB->query("SELECT ID FROM permissions WHERE Level=".db_string($LoggedUser['Class']));
	list($PermID) = $DB->next_record();
	

	$Points=bp_getpoints($LoggedUser['ID'],1);
?>
<style>
.v2_red{ color:red; }
.v2_green{ color:green; }

</style>
		<div class="box" id="bp_promo">
			<div class="pad">
				<p>Promotion</p>
				<table class="box">
					<tr class="colhead">
						<td style="width:50%;">Class</td>
						<td>Points</td>
						<td>Requirements</td>
						<td>Purchase</td>
					</tr>
					<? $i=0;

					foreach ($ClassCriteria as $Data) {
						if($PermID>=$Data['To']) continue;
						$i++;
						$Errors=array();
						if($LoggedUser['BytesUploaded']<$Data['MinUpload']) $Errors['Uploaded']="(".get_size(($Data['MinUpload']-$LoggedUser['BytesUploaded']))." left)";
						if($Uploads<$Data['MinUploads']) $Errors['Uploads']="(".number_format($Data['MinUploads']-$Uploads)." left)";
						if($LoggedUser['JoinDate']>$Data['MaxTime']) $Errors['Time']="(".time_df($Data['MaxTime'], $LoggedUser['JoinDate'])." left)";

						?>
						<tr>
							<td><h3><?=make_class_string($Data['To'])?></h3></td>
							<td><?=number_format($Data['PointReq'])?></td>
							<td>Uploaded: <span class="<?=($Errors['Uploaded'])?'v2_red':'v2_green'?>"><?=get_size($Data['MinUpload'])?><?=($Errors['Uploaded'])?' '.$Errors['Uploaded']:''?></span><br />
							<?if($Data['MinUploads']>0) { ?>Uploads:  <span class="<?=($Errors['Uploads'])?'v2_red':'v2_green'?>"><?=number_format($Data['MinUploads'])?><?=($Errors['Uploads'])?' '.$Errors['Uploads']:''?></span><br /><? } ?>

							Member For: <span class="<?=($Errors['Time'])?'v2_red':'v2_green'?>"><?=$Data['TimeReq']?><?=($Errors['Time'])?' '.$Errors['Time']:''?></span></td>
							<td><?=(1==1||($Data['From']==$PermID&&!$Errors&&$Points>$Data['PointReq']))?'<a href="points.php?action=purchase&type=6&itemid='.$Data['CatalogID'].'">Purchase</a>':'Not eligible'?></td>
						</tr>
					<? } ?>
					
					<? if (!$i) { ?>
						<tr>
							<td colspan="4">
								<strong>You are not eligible for any promotions. Check back later!</strong>
							</tr>
						</tr>
					<? } ?>
				</table>
			</div>
		</div>
<? } ?>