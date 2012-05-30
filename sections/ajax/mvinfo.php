<?
function format($v) {
	$v=html_entity_decode($v);
	$r=strtolower(str_replace(" ",".",trim($v)));
	$r=str_replace('r&b', 'rhythm.and.blues',$r);
	return $r;
}
// james@bandit.co.nz
function array_find($needle, $haystack, $search_keys = false) {
        if(!is_array($haystack)) return false;
        foreach($haystack as $key=>$value) {
            $what = ($search_keys) ? $key : $value;
            if(strpos($what, $needle)!==false) return $key;
        }
        return false;
}
// cloak as browser. required on full site (we use mobile as a workaround)
//ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11');
function get_contents($URL) {
//	$Response=file_get_contents($URL);
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $URL); 
	curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)'); 
	curl_setopt ($ch, CURLOPT_HEADER, 0); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	$Response = curl_exec ($ch); 
	curl_close ($ch); 

	return $Response;
}
if((isset($_REQUEST['artist']) && !empty($_REQUEST['artist'])) || (isset($_REQUEST['title']) && !empty($_REQUEST['title']))) {
	$Artist = $_REQUEST['artist'];
	$Title = $_REQUEST['title'];
} else {
	die("missing parameters");
}

// Mobile version - saves bandwidth and we don't want to hammer wikipedia's "special:search"
$URL="http://en.m.wikipedia.org/wiki?search=".urlencode($Artist)."+intitle:".urlencode($Title)." song";
$Contents=get_contents($URL);

// We need to find the link and the title
preg_match_all("/<div.+mw-search-result-heading\"><a href=\"(.*?)\" title=\"(.*?)\">.+<\/div>/i", $Contents, $Matches);
$WPUrl="";
$WPTitle="";
if(count($Matches[2])>1) {
	for($i=0;$i<count($Matches[2]);$i++) {
		if(strtolower($Matches[2][$i])!=strtolower($Title) && !stripos($Matches[2][$i],$Artist) && !stripos($Matches[2][$i],"(song)")) continue;
		$WPUrl=$Matches[1][$i];
		$WPTitle=$Matches[2][$i];
		break;
	}
	if(!$WPUrl&&!$WPTitle) {
		$WPUrl=$Matches[1][0];
		$WPTitle=$Matches[2][0];
	}
} else {
	$WPUrl = $Matches[1][0];
	$WPTitle = $Matches[2][0];
}
// Not a valid wikipedia URL?
if(!preg_match("/\/wiki\/.+/i",$WPUrl)) die("Page not found");
//$UrlizedTitle = urlencode(str_replace(" ","_", $Title));
if(!preg_match("/$Title/i",$Title,$Matches)) die("Title not found in URL");

$Page=get_contents("http://en.m.wikipedia.org$WPUrl");
$PageExp=explode("\n",$Page);

// Sort of dirty, but let's play ball
$Keys=array_find('<th scope="row" style="text-align:left;"><a href="/wiki/Music_genre" title="Music genre">Genre</a></th>',$PageExp);
preg_match("/>(.*?)<\/td>/i", $PageExp[$Keys+1], $Tags);
$Tags=explode(", ",$Tags[1]);
$Tags=preg_replace("/<sup.+<\/sup>/","",$Tags);
$Tags=preg_replace("/<a href=\".+\" title=\".+\">(.*?)<\/a>/i", "$1", $Tags);
$Tags=array_map('format',$Tags);
$Keys2=array_find('<th scope="row" style="text-align:left;">Released</th>',$PageExp);
if(!preg_match("/>.+ (\d{4})/i",$PageExp[$Keys2+1], $Year))
	preg_match("/>(\d{4})/i",$PageExp[$Keys2+1], $Year);
$Year=$Year[1];

// Get full image link (hax, sorta)
preg_match_all("%<img .+ src=\"(.*?)\"%i",$Page,$PageImages);
$Image='';
foreach($PageImages[1] as $VtrImage) {
	if(strpos($VtrImage,".svg") || strpos($VtrImage,"Ambox_content.png")) {
		continue;
	}
	if(preg_match("%upload\.wikimedia\.org\/wikipedia/[a-z]+\/[a-z]+\/[a-z0-9]\/[a-z0-9]{2}\/%i",$VtrImage)) {
		$Image=$VtrImage;
		break;
	}
}

if(preg_match("%\/thumb\/%i",$Image)) {
	$Image2=preg_replace(array("/thumb\//","/(png|jpg|jpeg|gif)\/.+$/"),"$1",$Image);
} else {
	$Image2=$Image;
}

if(strpos($Image2,"//")===0) $Image2="http:".$Image2;

if(!empty($Image2)) {
	$ch=curl_init("http://ptpimg.me/index.php?type=uploadv2&key=QT5LGz7ktGFVZpfFArVHCpEvDcC3qrUZrf0kP&uid=999999&url=c_h_e_c_k_p_o_s_t");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "urls=".$Image2);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$vx=json_decode($response);
	if($vx[0]->status==1)
		$Image3='http://ptpimg.me/'.$vx[0]->code.'.'.strtolower($vx[0]->ext);
	else
		$Image3=$Image2;
} else {
	$Image3="";
}

echo json_encode(array(array("tags"=>$Tags,"artwork"=>$Image3,"year"=>$Year)));
?>
