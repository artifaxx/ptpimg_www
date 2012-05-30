<?
// The object of this is to grab the screens out of mediainfo output and return the screens alone.
// That way they can paste dappinfo or other programs output into the mediainfo box and not have to copy/paste, etc
//$MediaInfo=$_REQUEST['MediaInfo'];
//if(empty($MediaInfo)) die();
$MediaInfo="General
Complete name                    : H:\Santogold - L.E.S. Artistes {dvb-s white}.VOB
Format                           : MPEG-PS
File size                        : 148 MiB
Duration                         : 3mn 28s
Overall bit rate                 : 5 970 Kbps

Video
ID                               : 224 (0xE0)
Format                           : MPEG Video
Format version                   : Version 2
Format profile                   : Main@Main
Format settings, BVOP            : Yes
Format settings, Matrix          : Custom
Format settings, GOP             : M=3, N=12
Duration                         : 3mn 28s
Bit rate mode                    : Variable
Bit rate                         : 5 403 Kbps
Nominal bit rate                 : 15.0 Mbps
Width                            : 720 pixels
Height                           : 576 pixels
Display aspect ratio             : 16:9
Frame rate                       : 25.000 fps
Standard                         : PAL
Color space                      : YUV
Chroma subsampling               : 4:2:0
Bit depth                        : 8 bits
Scan type                        : Interlaced
Scan order                       : Top Field First
Compression mode                 : Lossy
Bits/(Pixel*Frame)               : 0.521
Stream size                      : 134 MiB (91%)

Audio
ID                               : 189 (0xBD)-128 (0x80)
Format                           : AC-3
Format/Info                      : Audio Coding 3
Mode extension                   : CM (complete main)
Muxing mode                      : DVD-Video
Duration                         : 3mn 28s
Bit rate mode                    : Constant
Bit rate                         : 448 Kbps
Channel(s)                       : 2 channels
Channel positions                : Front: L R
Sampling rate                    : 48.0 KHz
Bit depth                        : 16 bits
Compression mode                 : Lossy
Delay relative to video          : 1ms
Stream size                      : 11.1 MiB (8%)

Menu


[img]http://ptpimg.me/zlr145.png[/img]
[img]http://ptpimg.me/l5ldbi.png[/img]
[img]http://ptpimg.me/0gh88m.png[/img]
";

preg_match_all("%".IMAGE_REGEX."%", $MediaInfo, $Images);
// These are the screens:
$Screens=implode("\n",$Images[0]);
$MediaInfo=preg_replace("%^.+".IMAGE_REGEX.".+$%m","",$MediaInfo);
echo trim($MediaInfo);
?>