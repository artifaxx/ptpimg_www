<?
enforce_login();
authorize();
if(!check_perms('torrents_check')) error(403);
if(!is_number($_GET['torrentid'])) die("404");
else $TorrentID=$_GET['torrentid'];

$DB->query("UPDATE torrents SET Checked='1' WHERE ID=".db_string($_GET['torrentid']));
$Cache->delete_value('checked_tor_'.$_GET['torrentid']);
$Cache->delete_value('num_torrent_unchecked');
?>
