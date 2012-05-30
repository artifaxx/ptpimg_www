<?
$Images[]=array();
function vx_error() {
	die(json_encode(array(array('status'=>0))));
}
if(!is_number($_GET['torrentid'])) die("404");
else $TorrentID=$_GET['torrentid'];
$DB->query("SELECT Resolution, Screens FROM torrents WHERE ID=$TorrentID");
list($Resolution, $Screens)=$DB->next_record();
$Screens=explode("\n",$Screens);
foreach($Screens as $Screen) {
	if(preg_match("%".IMAGE_REGEX."%i", $Screen, $Matches)) {
		$ch=curl_init("http://ptpimg.me/index.php?type=uploadv2&key=QT5LGz7ktGFVZpfFArVHCpEvDcC3qrUZrf0kP&uid=999999&url=c_h_e_c_k_p_o_s_t");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "urls=".$Matches[0]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch);
		$response=json_decode($response);
		$Images[]=$response[0]->code;
		$ImageURLs[]="http://ptpimg.me/".$response[0]->code.".".$response[0]->ext;
		list($w,$h,$t,$a)=getimagesize("/home/ptpimg/public_html/raw/".$response[0]->code);
		$ImgRes=$w.'x'.$h;
		$DB->query("INSERT INTO stored_res (url,resolution) values('".$response[0]->code."', '$ImgRes')");
	}

}
$DB->query("UPDATE torrents SET Screens='".implode("\n",$ImageURLs)."' WHERE ID=".$TorrentID." LIMIT 1");
if($ImgRes==$Resolution) {
	echo $ImgRes;
} else {
	echo "$ImgRes / $Resolution";
}
?>
