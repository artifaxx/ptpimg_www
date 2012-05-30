<?
show_header();
$DB->query("SELECT
	bal.TorrentID,
	GROUP_CONCAT(DISTINCT bal.UserID SEPARATOR ' ') AS UserID,
	(SUM(bal.Uploaded)-SUM(bal.Downloaded)) AS Balance
	FROM balance AS bal
	JOIN users_main AS um ON um.ID=bal.UserID
	WHERE um.Enabled='1'
	group by bal.TorrentID
	ORDER BY Balance DESC");
// Done calculating balance
$TorObj = $DB->to_array();
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
