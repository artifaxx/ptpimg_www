<?
authorize();
if(!isset($_GET['method']) || empty($_GET['method'])) die("error");
if(!isset($_GET['uid']) || empty($_GET['uid']) || !is_number($_GET['uid'])) die("error");
if(!isset($_GET['fid']) || empty($_GET['fid']) || !is_number($_GET['fid'])) die("error");
$UserID=db_string($_GET['uid']);
$TorrentID=db_string($_GET['fid']);
switch($_GET['method']) {
	case 'change':
		if(!isset($_GET['nh']) || empty($_GET['nh']) || !is_number($_GET['nh']) || !($_GET['nh']>0)) die("error");
		$Amount=db_string($_GET['nh']);
		$DB->query("SELECT ID,Requirement FROM users_seedreqs WHERE TorrentID=$TorrentID AND UserID=$UserID");
		list($EntryID)=$DB->next_record();
		if($EntryID) {
			$DB->query("UPDATE users_seedreqs SET Completed='".($Amount*60)."' WHERE ID=$EntryID LIMIT 1");
			die($Amount);
		}
	break;
	case 'activity':
		if(!isset($_GET['nh']) || empty($_GET['nh'])) die("error");
		if($_GET['nh']!="a" && $_GET['nh']!="i") die("error");
		$Var=db_string($_GET['nh']);
		$DB->query("SELECT ID FROM users_seedreqs WHERE TorrentID=$TorrentID AND UserID=$UserID");
		list($EntryID)=$DB->next_record();
		if($EntryID) {
			switch($Var) {
				case 'a':
					$DB->query("UPDATE users_seedreqs SET Status='0' WHERE ID=$EntryID LIMIT 1");
					break;
				case 'i':
					$DB->query("UPDATE users_seedreqs SET Status='2' WHERE ID=$EntryID LIMIT 1");
					break;
			}
			echo "ok";
		}
	break;
	default:
		die("error");
	break;
}
?>