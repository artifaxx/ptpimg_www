<?
show_header();
$DB->query("SELECT
            bal.TorrentID,
            bal.Uploaded,
            bal.Downloaded
            FROM balance AS bal
	    LEFT JOIN users_main AS um ON um.ID=bal.UserID
	    WHERE um.Enabled='1'");
$TorObj=array();
while(list($TorrentID,$Uploaded,$Downloaded)=$DB->next_record()) {
	$TorObj[$TorrentID]['Balance']+=$Uploaded;
	$TorObj[$TorrentID]['Balance']-=$Downloaded;
	$TorObj[$TorrentID]['TorrentID']=$TorrentID;
}
// Done calculating balance

function cmp($a, $b)
{
    if ($a['Balance'] == $b['Balance']) {
        return 0;
    }
    return ($a['Balance'] < $b['Balance']) ? -1 : 1;
}

usort($TorObj, "cmp");
?>
<div class="box">
<table>
	<tr class="rowa">
		<th>TorrentID</th>
		<th>Balance Amount</th>
	</tr>
<?
$Row='a';
foreach($TorObj as $Tor) {
	if($Tor['Balance']<52428800) continue;
?>
	<tr class="row<?=$Row?>">
		<td><a href="torrents.php?torrentid=<?=$Tor['TorrentID']?>"><?=$Tor['TorrentID']?></a></td>
		<td><?=get_size($Tor['Balance'])?></td>
	</tr>
<? } ?>
</table>
</div>
<?
show_footer();
?>
