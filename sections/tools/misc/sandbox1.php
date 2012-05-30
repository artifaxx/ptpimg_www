<?
die("mediainfo, check source");
// d8c1f6ae20a7b966f8c85850573a1a57
$MediaInfo="General
Unique ID : 214515783173572919587388029241588475749 (0xA1623CAE604083BF9E71A5E15A046765)
Complete name : H:\Pink-Fucking_Perfect-x264-2011-FRAY\pink-fucking_perfect-x264-2011-fray.mkv
Format : Matroska
File size : 37.4 MiB
Duration : 3mn 57s
Overall bit rate : 1 321 Kbps
Encoded date : UTC 2011-02-04 23:16:59
Writing application : mkvmerge v4.3.0 ('Escape from the Island') built on Sep 5 2010 10:30:51
Writing library : libebml v1.0.0 + libmatroska v1.0.0

Video
ID : 1
Format : AVC
Format/Info : Advanced Video Codec
Format profile : Main@L3.0
Format settings, CABAC : Yes
Format settings, ReFrames : 5 frames
Muxing mode : Header stripping
Codec ID : V_MPEG4/ISO/AVC
Duration : 3mn 57s
Width : 704 pixels
Height : 352 pixels
Display aspect ratio : 16:9
Frame rate : 23.976 fps
Color space : YUV
Chroma subsampling : 4:2:0
Bit depth : 8 bits
Scan type : Progressive
Writing library : x264 core 100 r1659 57b2e56
Encoding settings : cabac=1 / ref=5 / deblock=1:0:0 / analyse=0x1:0x111 / me=umh / subme=9 / psy=1 / psy_rd=1.00:0.00 / mixed_ref=1 / me_range=16 / chroma_me=1 / trellis=1 / 8x8dct=0 / cqm=0 / deadzone=21,11 / fast_pskip=1 / chroma_qp_offset=-2 / threads=6 / sliced_threads=0 / nr=0 / decimate=1 / interlaced=0 / constrained_intra=0 / bframes=3 / b_pyramid=2 / b_adapt=1 / b_bias=0 / direct=1 / weightb=1 / open_gop=0 / weightp=2 / keyint=250 / keyint_min=25 / scenecut=40 / intra_refresh=0 / rc_lookahead=40 / rc=crf / mbtree=1 / crf=18.0 / qcomp=0.60 / qpmin=10 / qpmax=51 / qpstep=4 / ip_ratio=1.40 / aq=1:1.00
Language : English

Audio
ID : 2
Format : MPEG Audio
Format version : Version 1
Format profile : Layer 3
Mode : Joint stereo
Mode extension : MS Stereo
Muxing mode : Header stripping
Codec ID : A_MPEG/L3
Codec ID/Hint : MP3
Duration : 3mn 57s
Bit rate mode : Variable
Channel(s) : 2 channels
Sampling rate : 44.1 KHz
Compression mode : Lossy";
$MediaInfo=preg_replace("/^[ \t]+$/m","", $MediaInfo);

require SERVER_ROOT.'/classes/class_mediainfo.php';
$IO = new MEDIAINFO;
$IO->setInfo($MediaInfo);
$IO->parse();
$Properties=$IO->getProperties();
print_r($Properties);
/*$DB->query("SELECT ID, UserID, Screens FROM torrents");
while(list($ID, $UserID, $Screens)=$DB->next_record()) {
	$Exp = explode("\n",$Screens);
	//if(count($Exp)<3) echo "$ID by $UserID only has ".count($Exp)." screenshot(s).<br />";
	for($i=0;$i<count($Exp);$i++) {
		// if it's hosted on ptpimg, let's check it, internally
		preg_match("/ptpimg\.me\/([a-z0-9]+)\./i",$Exp[$i],$Matches);
		$f=imagecreatefrompng("/home/ptpimg/public_html/raw/".$Matches[1]);
		$rgb=imagecolorat($f, 1, 1);
		if($rgb=="8487297")
			echo "<a href='torrents.php?torrentid=$ID'>$ID</a> : $UserID : $Exp[$i]<br />\n";
		
	}
}*/
?>