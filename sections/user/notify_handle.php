<?
if(!check_perms('site_torrents_notify')){ error(403); }
authorize();

$ArtistList = '';
$TagList = '';
$NotTagList = '';
$CategoryList = '';
$CodecList = '';
$ContainerList = '';
$ResolutionList = '';
$SourceList = '';
$FromYear = 0;
$ToYear = 0;
$HasFilter = false;

if($_POST['artists']){
	$Artists = explode(',', $_POST['artists']);
	$ParsedArtists = array();
	foreach($Artists as $Artist){
		if(trim($Artist) != "") {
			$ParsedArtists[] = db_string(trim($Artist));
		}
	}
	if(count($ParsedArtists) > 0) {
		$ArtistList = '|'.implode("|", $ParsedArtists).'|';
		$HasFilter = true;
	}
}

if($_POST['excludeva']){
	$ExcludeVA = '1';
	$HasFilter = true;
} else {
	$ExcludeVA = '0';
}

if($_POST['newgroupsonly']){
	$NewGroupsOnly = '1';
	$HasFilter = true;
} else {
	$NewGroupsOnly = '0';
}

if($_POST['tags']){
	$TagList = '|';
	$Tags = explode(',', $_POST['tags']);
	foreach($Tags as $Tag){
		$TagList.=db_string(trim($Tag)).'|';
	}
	$HasFilter = true;
}

if($_POST['nottags']){
	$NotTagList = '|';
	$Tags = explode(',', $_POST['nottags']);
	foreach($Tags as $Tag){
		$NotTagList.=db_string(trim($Tag)).'|';
	}
	$HasFilter = true;
}

if($_POST['categories']){
	$CategoryList = '|';
	foreach($_POST['categories'] as $Category){
		$CategoryList.=db_string(trim($Category)).'|';
	}
	$HasFilter = true;
}

if($_POST['codecs']){
	$CodecList = '|';
	foreach($_POST['codecs'] as $Codec){
		$CodecList.=db_string(trim($Codec)).'|';
	}
	$HasFilter = true;
}


if($_POST['containers']){
	$ContainerList = '|';
	foreach($_POST['containers'] as $Container){
		$ContainerList.=db_string(trim($Container)).'|';
	}
	$HasFilter = true;
}

if($_POST['resolutions']){
	$ResolutionList = '|';
	foreach($_POST['resolutions'] as $Resolution){
		$ResolutionList.=db_string(trim($Resolution)).'|';
	}
	$HasFilter = true;
}

if($_POST['sources']){
	$SourceList = '|';
	foreach($_POST['sources'] as $Source){
		$SourceList.=db_string(trim($Source)).'|';
	}
	$HasFilter = true;
}

if($_POST['fromyear'] && is_number($_POST['fromyear'])){
	$FromYear = db_string(trim($_POST['fromyear']));
	$HasFilter = true;
	if($_POST['toyear'] && is_number($_POST['toyear'])) {
		$ToYear = db_string(trim($_POST['toyear']));
	} else {
		$ToYear = date('Y')+3;
	}
}

if(!$HasFilter){
	$Err = 'You must add at least one criterion to filter by';
} elseif(!$_POST['label'] && !$_POST['id']) {
	$Err = 'You must add a label for the filter set';
}

if($Err){
	error($Err);
	header('Location: user.php?action=notify');
	die();
}

$ArtistList = str_replace('||','|',$ArtistList);
$TagList = str_replace('||','|',$TagList);
$NotTagList = str_replace('||','|',$NotTagList);

if($_POST['id'] && is_number($_POST['id'])){
	$DB->query("UPDATE users_notify_filters SET
		Artists='$ArtistList',
		ExcludeVA='$ExcludeVA',
		NewGroupsOnly='$NewGroupsOnly',
		Tags='$TagList',
		NotTags='$NotTagList',
		Categories='$CategoryList',
		Codecs='$CodecList',
		Containers='$ContainerList',
		Resolutions='$ResolutionList',
		Sources='$SourceList',
		FromYear='$FromYear',
		ToYear='$ToYear'
		WHERE ID='".db_string($_POST['id'])."' AND UserID='$LoggedUser[ID]'");
} else {
	$DB->query("INSERT INTO users_notify_filters 
		(UserID, Label, Artists, ExcludeVA, NewGroupsOnly, Tags, NotTags, Categories, Codecs, Containers, Resolutions, Sources, FromYear, ToYear)
		VALUES
		('$LoggedUser[ID]','".db_string($_POST['label'])."','$ArtistList','$ExcludeVA','$NewGroupsOnly','$TagList', '$NotTagList', '$CategoryList','$CodecList','$ContainerList','$ResolutionList','$SourceList', '$FromYear', '$ToYear')");
}

$Cache->delete_value('notify_filters_'.$LoggedUser['ID']);
if(($Notify = $Cache->get_value('notify_artists_'.$LoggedUser['ID'])) === FALSE || $Notify['ID'] == $_POST['id']) {
	$Cache->delete_value('notify_artists_'.$LoggedUser['ID']);
}
header('Location: user.php?action=notify');
?>
