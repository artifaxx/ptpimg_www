<?
authorize();

$AuthorizedUpdates=array("collapse:bph_table", "collapse:sreq_completed", "collapse:sreq_active", "collapse:sreq_inactive", "collapse:hp_last5");
if(!in_array($_GET['opt'], $AuthorizedUpdates)) die(); else $OptionName = $_GET['opt'];
if(!isset($_GET['val']) || empty($_GET['val'])) die(); else $OptionVal = db_string($_GET['val']);
$DB->query("SELECT SiteOptions FROM users_info WHERE UserID='".db_string($LoggedUser['ID'])."'");
list($SiteOptions)=$DB->next_record(MYSQLI_NUM, true);
$SiteOptions=unserialize($SiteOptions);
$SiteOptions[$OptionName]=$OptionVal;
$DB->query("UPDATE users_info SET SiteOptions='".db_string(serialize($SiteOptions))."' WHERE UserID='".db_string($LoggedUser['ID'])."'");
$Cache->begin_transaction('user_info_heavy_'.$LoggedUser['ID']);
$Cache->update_row(false, array($OptionName=>$OptionVal));
$Cache->commit_transaction(0);
?>