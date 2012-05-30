<?
include_once(SERVER_ROOT.'/classes/class_feed.php');
include_once(SERVER_ROOT.'/classes/class_text.php');
$Feed = new FEED;
$Text = new TEXT;
//******************************************************************************//
//--------------- Add a recommendation -----------------------------------------//
if(!check_perms('site_recommend_own') && !check_perms('site_manage_recommendations')){
	error(403);
}

$URL = trim($_POST['url']);

// Make sure the URL they entered is on our site, and is a link to a torrent
$URLRegex = '/^https?:\/\/(www\.|ssl\.)?'.NONSSL_SITE_URL.'\/torrents\.php\?id=([0-9]+)$/i';
$Val->SetFields('url',
			'1','regex','The URL must be a link to a torrent on the site.',array('regex'=>$URLRegex));
$Err = $Val->ValidateForm($_POST); // Validate the form

if($Err){ // if something didn't validate
	error($Err);
	header('Location: '.$_SERVER['HTTP_REFERER']);
	exit;
}

// Get torrent ID
$URLRegex = '/torrents\.php\?id=([0-9]+)$/i';
preg_match($URLRegex, $URL, $Matches);
$GroupID=$Matches[1];

if(empty($GroupID) || !is_number($GroupID)) {
	 error(404); 
}

if(!$GroupID){ error(404); }
$DB->query("INSERT INTO torrents_recommended (Time, GroupID, UserID, Active, Length, Review, Bonus)
VALUES ('".sqltime()."',
'".db_string($GroupID)."',
$LoggedUser[ID],
'0',
'".db_string($_POST['length'])."','".db_string($_POST['review'])."',
'".db_string($_POST['bonus'])."')");
header('Location: '.$_SERVER['HTTP_REFERER']);
?>