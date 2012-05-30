<?
die("check source code, bp system");
// Bonus points collection

// Mathematics:
// Torrent Size, Weeks Seeded, Number of Seeds
/*
GB x(1+(weeks seeded)/4))x(seed multiplier)
Seed Multiplier

0-1GB
4 seeds = 1.4
3 seeds = 2.0
2 seeds = 2.8
1 seed = 4

1-5GB
4 seeds = 1.2
3 seeds = 1.7
2 seeds = 2.3
1 seed = 3.0

5GB+
4 seeds = 1.2
3 seeds = 1.4
2 seeds = 1.6
1 seed = 2.0
*/
$Origin=$DB->query("SELECT xfu.fid, xfu.uid, t.Seeders,
			t.Size,
			usr.Completed,
			ubpm.Modifier
			FROM xbt_files_users AS xfu
			LEFT JOIN torrents AS t ON t.ID=xfu.fid
			LEFT JOIN users_seedreqs AS usr ON (usr.UserID=xfu.uid AND usr.TorrentID=xfu.fid)
			LEFT JOIN users_bpmodifiers AS ubpm ON (ubpm.UserID=xfu.fid AND ubpm.TorrentID=xfu.fid)");

// People to give points to
while(list($TorrentID, $UserID, $SeedCount, $Size, $SeedTime, $SpecialModifier)=$DB->next_record()) {
	if($SeedTime>161280) // Cap at 16 weeks
		$SeedTime=161280;
		
	if($SeedTime<10080) // Less than one week, no modifier
		$TimeModifier=0;
	else
		$TimeModifier=floor($SeedTime/10080);
	
	// Find amount, in gigabytes
	$SizeModifier=($Size/1073741824);
	
	if($SizeModifier<1) 
		$SeedModifier=array(1=>4.0, 2=>2.8, 3=>2.0, 4=>1.4);
	elseif($SizeModifier>=1 && $SizeModifier<5) 
		$SeedModifier=array(1=>3.0, 2=>2.3, 3=>1.7, 4=>1.2);
	elseif($SizeModifier>=5) 
		$SeedModifier=array(1=>2.0, 2=>1.6, 3=>1.4, 4=>1.2);
	
	
	$Points=$SizeModifier*(1+($TimeModifier)/4)*$SeedModifier[$SeedCount];
	if($SpecialModifier) $Points*=$SpecialModifier;
	
	// Runs every 15, so...
	$Points/=4;
	//echo "giving $Points to ".$UserID." (Size: $SizeModifier Time: $TimeModifier Seed: $SeedModifier[$SeedCount])<br />";
	$DB->query("INSERT INTO users_bp (UserID, Points) VALUES($UserID, $Points)
				ON DUPLICATE KEY UPDATE Points=Points+$Points");
	$DB->set_query_id($Origin);
}

?>