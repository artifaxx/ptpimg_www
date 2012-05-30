<?
$MediaInfo=$_POST['data'];
$MediaInfo=preg_replace("/^[ \t]+$/m","", $MediaInfo);

require SERVER_ROOT.'/classes/class_mediainfo.php';
$IO = new MEDIAINFO;
$IO->setInfo($MediaInfo);
$IO->parse();
$Properties=$IO->getProperties();

// Container first!
$Container="";
switch($Properties[0]['Format']) {
	case 'MPEG-PS':
		$Container="VOB";
		break;
	case 'AVI':
		$Container="AVI";
		break;
	case 'MPEG-4':
		$Container="MP4";
		break;
	case 'Matroska':
		$Container="MKV";
		break;
	case 'MPEG-TS':
		$Container="TS";
		break;
}
// Codec
$Codec="";
// Find XviD or DivX
if($Container=="AVI" && $Properties[1]["Codec ID"]="XVID") {
	$Codec="XviD";
}else if($Container=="AVI" && preg_match("/DX\d{2}/",$Properties[1]["Codec ID"])) {
	$Codec="DivX";
}else if($Container=="MKV" && stripos($Properties[1]["Writing library"],"x264 core")!==false) {
	$Codec="x264";
}else if($Container=="VOB") {
	$Codec="MPEG";
}else if($Container=="MP4" && (isset($Properties[0]["gsst"]) || isset($Properties[0]["gstd"]) || isset($Properties[0]["gssd"]) || isset($Properties[0]["gshh"]))) {
	$Codec="H.264";
	$Source="WEB";
}
// Audio
$Audio="";
if($Properties[2]["Format"]=="FLAC") {
	$Audio="FLAC";
}else if($Properties[2]["Format"]=="MPEG Audio") {
	if($Properties[2]["Format profile"]=="Layer 3") {
		$Audio="MP3";
	}else if($Properties[2]["Format profile"]=="Layer 2") {
		$Audio="MP2";
	}else if($Properties[2]["Format profile"]=="Layer 1") {
		$Audio="MP1";
	}
}else if($Properties[2]["Format"]=="AAC") {
	$Audio="AAC";
}else if($Properties[2]["Format"]=="AC-3" || $Properties[2]["Format"]=="AC3") {
	$Audio="AC3";
}else if($Properties[2]["Format"]=="DTS") {
	$Audio="DTS";
}else if($Properties[2]["Format"]=="PCM") {
        $Audio="PCM";
}


// Resolution
$Width=$Properties[1]["Width"];
$Height=$Properties[1]["Height"];
preg_match("/([0-9 ]+) pixels/",$Width,$M);
$Width=str_replace(" ", "", $M[1]);
preg_match("/([0-9 ]+) pixels/",$Height,$M);
$Height=str_replace(" ", "", $M[1]);

// Resolution
if ($Width==1920 && $Height==1080) $Resolution="1080p";
else if ($Width>1280 && $Height>720) $Resolution="1080p";
else if ($Width==1280 && $Height==720) $Resolution="720p";
else if (($Width-1280) < 50 && ($Height-720)<50 && $Height>600) $Resolution="720p";
else $Resolution=$Width."x".$Height;

// Source
// Okay, so I realize this isn't perfect.. but I'm going to use my experience here
if(!$Source) {
	if($Resolution=="640x480" || $Resolution=="720x480" || $Resolution=="720x576") $Source="DVD";
	else if($Resolution=="720p" || $Resolution=="1080p") {
		if($Properties[1]["Scan type"]=="Interlaced") {
			if($Resolution=="1080p")
				$Resolution="1080i";
			$Source="HDTV";
		} else {
			if(isset($Properties[0]['Complete name'])) {
				if(stripos($Properties[0]['Complete name'],'web'))
					$Source="WEB";
				else if(stripos($Properties[0]['Complete name'],'blu-ray') || stripos($Properties[0]['Complete name'],'bluray'))
					$Source="Blu-ray";
				else
					$Source="WEB";
			} else {
				$Source="WEB";
			}
		}
	}
	else $Source="DVD";
}
if(!strpos($Resolution,"x"))$quality="High Definition"; else $quality="Standard Definition";

// Did they include images?
if(preg_match_all("%".IMAGE_REGEX."%", $MediaInfo, $Images)) {
// These are the screens:
	$Screens=implode("\n",$Images[0]);
	$MediaInfo=preg_replace("%^.+".IMAGE_REGEX.".+$%m","",$MediaInfo);
} else {
	$Screens="";
}
$MediaInfo=trim($MediaInfo);
echo json_encode(array(array("codec"=>$Codec, "audio"=>$Audio, "container"=>$Container, "resolution"=>$Resolution, "source"=>$Source, "quality"=>$quality),array("mediainfo"=>$MediaInfo,"screens"=>$Screens)));
?>
