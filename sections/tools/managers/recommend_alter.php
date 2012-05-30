<?
//******************************************************************************//
//--------------- Delete a recommendation --------------------------------------//

if(!check_perms('site_recommend_own') && !check_perms('site_manage_recommendations')){
	error(403);
}

$GroupID=$_GET['groupid'];
if(!$GroupID || !is_number($GroupID)) { error(404); }

if(!check_perms('site_manage_recommendations')){
	$DB->query("SELECT UserID FROM torrents_recommended WHERE GroupID='$GroupID'");
	list($UserID) = $DB->next_record();
	if($UserID != $LoggedUser['ID']){
		error(403);
	}
}

$DB->query("DELETE FROM torrents_recommended WHERE GroupID='$GroupID'");
$DB->query("UPDATE torrents SET PointBonus='1' WHERE GroupID = ".db_string($GroupID));
$Cache->delete_value('detail_'.$GroupID.'_');
$Cache->delete_value('recommended');
header('Location: '.$_SERVER['HTTP_REFERER']);
?>