<?
die("tr");
$DB->query("SELECT tr.GroupID, tr.UserID, tr.Time, tr.Expires, tr.Active, tr.Length, tr.Bonus
FROM torrents_recommended AS tr
WHERE tr.Active='0'");

$RecommendList=$DB->to_array();
while (list($Key,list($GroupID, $UserID, $Time, $Expires, $Active, $Length, $Bonus)) = each($RecommendList)) {
        $DB->query("SELECT m.Username FROM users_main AS m WHERE m.ID='$UserID'");
        list($Username)=$DB->next_record();
        $DB->query("SELECT Name,WikiBody FROM torrents_group WHERE ID='".db_string($GroupID)."'");
        list($Name, $Description) = $DB->next_record();
        $DB->query("SELECT MAX(Sort) FROM collages_torrents WHERE CollageID='$Staffcollectionid'");
        list($Sort) = $DB->next_record();
        $Sort+=10;
        $DB->query("SELECT Name FROM collages WHERE ID='$Staffcollectionid'");
        list($Collection_Name) = $DB->next_record();
        $DB->query("SELECT GroupID FROM collages_torrents WHERE CollageID='$Staffcollectionid' AND GroupID='$GroupID'");
        if($DB->record_count() == 0) {
                $DB->query("INSERT IGNORE INTO collages_torrents
                        (CollageID, GroupID, UserID, Sort)
                        VALUES
                        ('$Staffcollectionid', '$GroupID', '$UserID', '$Sort')");

                $DB->query("UPDATE collages SET NumTorrents=NumTorrents+1 WHERE ID='$Staffcollectionid'");
                $Cache->delete_value('collage_'.$Staffcollectionid);
                write_log("Torrent $GroupID ($Name) was added to collection $Staffcollectionid ($Collection_Name) by " . $Username);
        }
        $DB->query("UPDATE torrents SET PointBonus='".$Bonus."' WHERE GroupID = ".db_string($GroupID));

        if ($Length) {
                $f=mktime()+($Length*60*60);
                $m=date('i',mktime());
                if ($m==0 || $m > 45)
                        $Expires=gmdate('Y-m-d H:00:00', $f);
                elseif ($m<=15)
                        $Expires=gmdate('Y-m-d H:15:00', $f);
                elseif ($m<=30)
                        $Expires=gmdate('Y-m-d H:30:00', $f);
                elseif ($m<=45)
                        $Expires=gmdate('Y-m-d H:45:00', $f);

                $DB->query("UPDATE torrents_recommended SET Expires='".$Expires."' WHERE GroupID = ".db_string($GroupID));
        }

        $DB->query("UPDATE torrents_recommended SET Active='1', DisplayHomePage='1' WHERE GroupID = ".db_string($GroupID));

        $Cache->delete_value('detail_'.$GroupID.'_');
        $Cache->delete_value('collage_'.$Staffcollectionid);
		$Cache->delete_value('recommend');
		$Cache->delete_value('recommend_artists');
		$Cache->cache_value('recommended_'.$GroupID, $Expires, 0);
}

// Remove old staff picks from freeleech
$t=gmdate('Y-m-d H:i:s', time());
        $DB->query("SELECT tr.GroupID
FROM torrents_recommended AS tr
WHERE tr.Expires < '$t' AND tr.Active='1'");
        while (list($GroupID, $UserID) = $DB->next_record()) {
			$DB->query("DELETE FROM torrents_recommended WHERE GroupID='$GroupID'");
            $Cache->delete_value('detail_'.$GroupID.'_'); // Wtf is this z?
			$Cache->delete_value('recommended_'.$GroupID);
			$Cache->delete_value('torrent_group_'.$GroupID);
			$Cache->delete_value('torrents_details_'.$GroupID);
			$Cache->delete_value('recommend');
			$Cache->delete_value('recommend_artists');
        }


// Redo cache values for recommended

$DB->query("SELECT tr.GroupID, tr.Expires
FROM torrents_recommended AS tr
WHERE tr.Active='1'");
$RecommendList=$DB->to_array();
while (list($Key,list($GroupID, $Expires)) = each($RecommendList)) {
	$Cache->cache_value('recommended_'.$GroupID, $Expires, 0);
}
show_footer();
?>